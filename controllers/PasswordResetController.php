<?php

class PasswordResetController extends Controller {
    private $userModel;
    private $emailService;

    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->emailService = new EmailService();
    }

    public function request() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');

            $errors = [];
            if (empty($email)) $errors[] = "Email is required";
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
            elseif (!$this->userModel->emailExists($email)) $errors[] = "Email not found";

            if (empty($errors)) {
                $token = $this->userModel->createResetToken($email);
                if ($token) {
                    $resetUrl = $this->getBaseUrl() . "/password/reset/$token";
                    $body = "Click to reset your password: <a href='$resetUrl'>$resetUrl</a>";
                    $this->emailService->send($email, "Reset Your Password", $body);
                    $this->render('reset_request', ['success' => 'Reset link sent to your email.']);
                } else {
                    $errors[] = "Failed to generate reset link";
                }
            }
            if (!empty($errors)) {
                $this->render('reset_request', ['errors' => $errors, 'email' => $email]);
            }
        } else {
            $this->render('reset_request');
        }
    }

    public function reset($token = null) {
        if (!$token || !$userId = $this->userModel->verifyResetToken($token)) {
            $this->render('password_reset', ['error' => 'Invalid or expired reset link']);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            $errors = [];
            if (empty($password)) $errors[] = "Password is required";
            elseif (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";
            if (empty($confirmPassword)) $errors[] = "Confirm password is required";
            elseif ($password !== $confirmPassword) $errors[] = "Passwords do not match";

            if (empty($errors)) {
                $this->userModel->updatePassword($userId, $password);
                $this->redirect('/auth/login'); // Updated to redirect
            } else {
                $this->render('password_reset', ['errors' => $errors, 'token' => $token]);
            }
        } else {
            $this->render('password_reset', ['token' => $token]);
        }
    }
}