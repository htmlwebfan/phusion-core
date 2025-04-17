<?php

require_once __DIR__ . '/../lib/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../lib/PHPMailer/src/SMTP.php';
require_once __DIR__ . '/../lib/PHPMailer/src/Exception.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class EmailService {
    private $config;

    public function __construct() {
        $this->config = include __DIR__ . '/../config.php';
    }

    public function send($to, $subject, $body) {
        $mail = new PHPMailer(true);
        try {
            // SMTP settings from config
            $mail->isSMTP();
            $mail->Host = $this->config['email']['host'];
            $mail->SMTPAuth = true;
            $mail->Username = $this->config['email']['username'];
            $mail->Password = $this->config['email']['password'];
            $mail->SMTPSecure = $this->config['email']['encryption'];
            $mail->Port = $this->config['email']['port'];

            // Email details
            $mail->setFrom($this->config['email']['from'], 'My MVC App');
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->Subject = $subject;
            $mail->Body = $body;

            $mail->send();
            return true;
        } catch (Exception $e) {
            error_log("Email failed: {$mail->ErrorInfo}");
            return false;
        }
    }
}