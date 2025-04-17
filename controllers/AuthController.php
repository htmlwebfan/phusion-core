<?php

class AuthController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';

            $errors = [];
            if (empty($email)) $errors[] = "Email is required";
            elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Invalid email format";
            if (empty($password)) $errors[] = "Password is required";

            if (empty($errors)) {
                $user = $this->userModel->login($email, $password);
                if ($user) {
                    $this->sessionModel->setUser($user);
                    $this->redirect('home');
                } else {
                    $errors[] = "Invalid credentials or unverified account";
                }
            }
            $this->render('login', ['errors' => $errors, 'email' => $email]);
        } else {
            $this->render('login');
        }
    }

    public function logout() {
        $this->sessionModel->logout();
        $this->redirect('auth/login');
    }
}