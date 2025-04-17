<?php

class RegisterController extends Controller {
    private $userModel;
    private $emailService;

    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
        $this->emailService = new EmailService();
    }

    public function index() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $errors = [];
            if (empty($name)) $errors[] = "Name is required";
            elseif (strlen($name) < 2) $errors[] = "Name must be at least 2 characters";
            if (empty($email)) $errors[] = "Email is required";
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
            elseif ($this->userModel->emailExists($email)) $errors[] = "Email already registered";
            if (empty($password)) $errors[] = "Password is required";
            elseif (strlen($password) < 6) $errors[] = "Password must be at least 6 characters";

            if (empty($errors)) {
                $userData = $this->userModel->addUser($name, $email, $password);
                $verifyUrl = $this->getBaseUrl() . "/register/verify/{$userData['token']}";
                $body = "Click to verify your email: <a href='$verifyUrl'>$verifyUrl</a>";
                $this->emailService->send($email, "Verify Your Email Address", $body);
                $this->render('register', ['success' => 'Registration successful! Check your email.']);
            } else {
                $this->render('register', ['errors' => $errors, 'name' => $name, 'email' => $email]);
            }
        } else {
            $this->render('register');
        }
    }

    public function verify($token = null) {
        if ($token && $this->userModel->verifyEmail($token)) {
            $this->render('verify', ['success' => 'Email verified! You can now log in.']);
        } else {
            $this->render('verify', ['error' => 'Invalid or expired verification link']);
        }
    }
}