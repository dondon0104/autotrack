<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class Webhook extends Controller
{
    public function __construct()
    {
        parent::__construct();
    }

    private function jsonResponse($code, $data){ http_response_code($code); header('Content-Type: application/json'); echo json_encode($data); exit; }

    public function xendit()
    {
        $token = $_SERVER['HTTP_X_CALLBACK_TOKEN'] ?? $_SERVER['HTTP_X-CALLBACK-TOKEN'] ?? '';
        $expected = config_item('xendit_callback_token') ?: '';
        if (!$expected || $token !== $expected) {
            $this->jsonResponse(401, ['ok'=>false,'message'=>'Invalid callback token']);
        }

        $raw = file_get_contents('php://input');
        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            $this->jsonResponse(400, ['ok'=>false,'message'=>'Invalid JSON']);
        }

        // Expecting ewallet charge webhook
        $data = $payload['data'] ?? [];
        $status = $data['status'] ?? '';
        $referenceId = $data['reference_id'] ?? '';

        if (!$referenceId) {
            $this->jsonResponse(400, ['ok'=>false,'message'=>'Missing reference_id']);
        }

        // Find pending payment by transaction_id (we stored reference_id there)
        $payment = $this->PaymentModel->getByTransactionId($referenceId);
        if (!$payment) {
            // Not found; ignore or log
            $this->jsonResponse(200, ['ok'=>true,'message'=>'No matching payment']);
        }

        if (strtoupper($status) === 'SUCCEEDED' || strtolower($status) === 'succeeded') {
            $this->PaymentModel->updateStatus($payment['id'], 'completed');
        } elseif (strtoupper($status) === 'FAILED' || strtolower($status) === 'failed') {
            $this->PaymentModel->updateStatus($payment['id'], 'failed');
        } else {
            // pending or other statuses -> leave as is
        }

        $this->jsonResponse(200, ['ok'=>true]);
    }

    public function paymongo()
    {
        $sigHeader = $_SERVER['HTTP_PAYMONGO_SIGNATURE'] ?? $_SERVER['HTTP_PAYMONGO-SIGNATURE'] ?? '';
        $secret = config_item('paymongo_webhook_secret') ?: '';
        if (!$secret) { $this->jsonResponse(401, ['ok'=>false,'message'=>'Webhook secret not configured']); }

        $raw = file_get_contents('php://input');
        if (!$this->verifyPayMongoSignature($sigHeader, $raw, $secret)) {
            $this->jsonResponse(401, ['ok'=>false,'message'=>'Invalid signature']);
        }

        $payload = json_decode($raw, true);
        if (!is_array($payload)) {
            $this->jsonResponse(400, ['ok'=>false,'message'=>'Invalid JSON']);
        }

        $type = $payload['data']['attributes']['type'] ?? '';
        if ($type === 'source.chargeable') {
            // Create a payment with the source id
            $sourceId = $payload['data']['attributes']['data']['id'] ?? '';
            $amount = (int)($payload['data']['attributes']['data']['attributes']['amount'] ?? 0);
            if ($sourceId && $amount > 0) {
                $this->createPayMongoPayment($sourceId, $amount);
            }
        } elseif ($type === 'payment.paid') {
            // Mark payment as completed
            $paymentIdPm = $payload['data']['id'] ?? '';
            $sourceId = $payload['data']['attributes']['source']['id'] ?? '';
            // We saved transaction_id as source id/reference id
            if ($sourceId) {
                $payment = $this->PaymentModel->getByTransactionId($sourceId);
                if ($payment) {
                    $this->PaymentModel->updateStatus($payment['id'], 'completed', $paymentIdPm);
                }
            }
        }

        $this->jsonResponse(200, ['ok'=>true]);
    }

    private function verifyPayMongoSignature($header, $payload, $secret)
    {
        // Header format: t=timestamp, s0=signature
        $parts = [];
        foreach (explode(',', $header) as $pair) {
            $kv = array_map('trim', explode('=', $pair, 2));
            if (count($kv) === 2) { $parts[$kv[0]] = $kv[1]; }
        }
        if (empty($parts['t']) || empty($parts['s0'])) return false;
        $signedPayload = $parts['t'] . '.' . $payload;
        $expected = hash_hmac('sha256', $signedPayload, $secret);
        return hash_equals($expected, $parts['s0']);
    }

    private function createPayMongoPayment($sourceId, $amount)
    {
        $secret = config_item('paymongo_secret_key');
        if (!$secret) return false;
        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => (int)$amount,
                    'currency' => 'PHP',
                    'source' => [ 'type' => 'source', 'id' => $sourceId ]
                ]
            ]
        ];
        $ch = curl_init('https://api.paymongo.com/v1/payments');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($secret . ':')
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        $resp = curl_exec($ch);
        curl_close($ch);
        return $resp !== false;
    }
}
?>
