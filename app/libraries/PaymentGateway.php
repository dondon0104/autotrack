<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

class PaymentGateway
{
    protected $provider;
    // Xendit
    protected $xenditKey;
    // PayMongo
    protected $pmSecret;
    // Common
    protected $successUrl;
    protected $failureUrl;

    public function __construct()
    {
        $this->provider = config_item('gcash_provider') ?: 'paymongo';
        $this->xenditKey = config_item('xendit_secret_key');
        $this->pmSecret = config_item('paymongo_secret_key');
        // Reuse same redirect URLs
        $this->successUrl = config_item('xendit_success_url') ?: config_item('base_url') . 'user/my-rentals';
        $this->failureUrl = config_item('xendit_failure_url') ?: config_item('base_url');
    }

    /**
     * Create a GCash charge and return [reference_id, checkout_url]
     */
    public function createGCashCharge($rental, $amount)
    {
        if ($this->provider === 'paymongo') {
            return $this->createGCashChargePayMongo($rental, $amount);
        }
        return $this->createGCashChargeXendit($rental, $amount);
    }

    private function createGCashChargeXendit($rental, $amount)
    {
        if (empty($this->xenditKey)) {
            throw new Exception('Xendit secret key not configured');
        }
        $referenceId = 'rent_' . (int)$rental['id'] . '_' . bin2hex(random_bytes(4));
        $payload = [
            'reference_id' => $referenceId,
            'currency' => 'PHP',
            'amount' => (float)$amount,
            'checkout_method' => 'ONE_TIME_PAYMENT',
            'channel_code' => 'PH_GCASH',
            'channel_properties' => [
                'success_redirect_url' => $this->successUrl,
                'failure_redirect_url' => $this->failureUrl
            ],
            'metadata' => [
                'rental_id' => (int)$rental['id'],
                'user_id' => (int)$rental['user_id']
            ]
        ];

        $ch = curl_init('https://api.xendit.co/ewallets/charges');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_USERPWD, $this->xenditKey . ':');
        curl_setopt($ch, CURLOPT_HTTPHEADER, [ 'Content-Type: application/json' ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($resp === false || $http >= 400) {
            throw new Exception('Xendit error: ' . ($err ?: $resp));
        }

        $data = json_decode($resp, true);
        if (!is_array($data)) {
            throw new Exception('Invalid response from Xendit');
        }
        $url = $data['actions']['mobile_web_checkout_url'] ?? ($data['actions']['desktop_web_checkout_url'] ?? null);
        if (!$url) {
            throw new Exception('No checkout URL returned from Xendit');
        }
        return [ 'reference_id' => $referenceId, 'checkout_url' => $url ];
    }

    private function createGCashChargePayMongo($rental, $amount)
    {
        if (empty($this->pmSecret)) {
            throw new Exception('PayMongo secret key not configured');
        }
        $payload = [
            'data' => [
                'attributes' => [
                    'amount' => (int)round($amount * 100), // centavos
                    'currency' => 'PHP',
                    'type' => 'gcash',
                    'redirect' => [
                        'success' => $this->successUrl,
                        'failed' => $this->failureUrl
                    ],
                    'metadata' => [
                        'rental_id' => (int)$rental['id'],
                        'user_id' => (int)$rental['user_id']
                    ]
                ],
                'type' => 'source'
            ]
        ];

        $ch = curl_init('https://api.paymongo.com/v1/sources');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($this->pmSecret . ':')
        ]);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));

        $resp = curl_exec($ch);
        $http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $err = curl_error($ch);
        curl_close($ch);

        if ($resp === false || $http >= 400) {
            throw new Exception('PayMongo error: ' . ($err ?: $resp));
        }
        $data = json_decode($resp, true);
        if (!is_array($data) || empty($data['data']['id'])) {
            throw new Exception('Invalid response from PayMongo');
        }
        $sourceId = $data['data']['id'];
        $url = $data['data']['attributes']['redirect']['checkout_url'] ?? null;
        if (!$url) {
            throw new Exception('No checkout URL returned from PayMongo');
        }
        return [ 'reference_id' => $sourceId, 'checkout_url' => $url ];
    }
}
