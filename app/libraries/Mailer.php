<?php
defined('PREVENT_DIRECT_ACCESS') OR exit('No direct script access allowed');

/**
 * Mailer wrapper that prefers PHPMailer (via Composer) and falls back to built-in Email library
 */
class Mailer {
    protected $_lava;
    protected $config;

    public function __construct()
    {
        $this->_lava = lava_instance();
        // load email config
        $this->config = [];
        $cfgPath = APP_DIR . 'config' . DIRECTORY_SEPARATOR . 'email.php';
        if (file_exists($cfgPath)) {
            $this->config = include $cfgPath;
        }
    }

    /**
     * Send an email
     * @param string $to Recipient email
     * @param string $subject Subject
     * @param string $htmlBody HTML body (plain accepted too)
     * @param string|null $from Optional from address
     * @param string|null $fromName Optional from name
     * @return bool
     */
    public function send($to, $subject, $htmlBody, $from = null, $fromName = null, $attachments = null)
    {
        // Try PHPMailer (Composer autoload required)
        if (class_exists('PHPMailer\\PHPMailer\\PHPMailer')) {
            return $this->sendWithPHPMailer($to, $subject, $htmlBody, $from, $fromName, $attachments);
        }
        // Fallback to built-in Email library
        $this->_lava->call->library('Email');
        try {
            $fromAddr = $from ?: ($this->config['from_address'] ?? 'no-reply@localhost');
            $fromName = $fromName ?: ($this->config['from_name'] ?? 'CarRental');
            $this->_lava->email->sender($fromAddr, $fromName);
            $this->_lava->email->recipient($to);
            $this->_lava->email->subject($subject);
            $this->_lava->email->email_content($htmlBody, 'html');
            return (bool)$this->_lava->email->send();
        } catch (\Throwable $e) {
            return false;
        }
    }

    protected function sendWithPHPMailer($to, $subject, $htmlBody, $from = null, $fromName = null, $attachments = null)
    {
        $mail = new \PHPMailer\PHPMailer\PHPMailer(true);
        $cfg = $this->config;
        try {
            // Server settings
            $driver = $cfg['driver'] ?? 'smtp';
            if ($driver === 'smtp') {
                $mail->isSMTP();
                $mail->Host       = $cfg['smtp_host'] ?? 'smtp.gmail.com';
                $mail->Port       = (int)($cfg['smtp_port'] ?? 587);
                $secure           = $cfg['smtp_secure'] ?? 'tls';
                if ($secure) { $mail->SMTPSecure = $secure; }
                $mail->SMTPAuth   = true;
                $mail->Username   = $cfg['smtp_user'] ?? '';
                $mail->Password   = $cfg['smtp_pass'] ?? '';
                if (!empty($cfg['debug'])) {
                    // Avoid polluting HTTP responses; route debug to error_log
                    $mail->SMTPDebug = 2;
                    $mail->Debugoutput = 'error_log';
                }
            }

            // Recipients
            $fromAddr = $from ?: ($cfg['from_address'] ?? 'no-reply@localhost');
            $fromName = $fromName ?: ($cfg['from_name'] ?? 'CarRental');
            // Gmail SMTP requires using your account or a verified alias as From
            $hostLower = strtolower($cfg['smtp_host'] ?? '');
            if ((strpos($hostLower, 'gmail') !== false || strpos($hostLower, 'google') !== false)
                && !empty($cfg['smtp_user']) && strtolower($fromAddr) !== strtolower($cfg['smtp_user'])) {
                $fromAddr = $cfg['smtp_user'];
            }
            $mail->setFrom($fromAddr, $fromName);
            $mail->addAddress($to);
            if (!empty($cfg['reply_to'])) $mail->addReplyTo($cfg['reply_to']);

            // Content
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body    = $htmlBody;

            // Attachments (array or string)
            if ($attachments) {
                if (is_string($attachments)) {
                    if (is_file($attachments)) $mail->addAttachment($attachments);
                } elseif (is_array($attachments)) {
                    foreach ($attachments as $att) {
                        if (is_string($att)) {
                            if (is_file($att)) $mail->addAttachment($att);
                        } elseif (is_array($att)) {
                            $path = $att['path'] ?? null;
                            $name = $att['name'] ?? '';
                            if ($path && is_file($path)) $mail->addAttachment($path, $name ?: null);
                        }
                    }
                }
            }

            return $mail->send();
        } catch (\Throwable $e) {
            return false;
        }
    }
}
