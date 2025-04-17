<?php

class SessionModel extends Model {
    public function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function setUser($user) {
        $this->startSession();
        $_SESSION['user'] = $user;
    }

    public function getUser() {
        $this->startSession();
        return $_SESSION['user'] ?? null;
    }

    public function isLoggedIn() {
        return $this->getUser() !== null;
    }

    public function isAdmin() {
        $user = $this->getUser();
        return $user && $user['role'] === 'admin';
    }

    public function logout() {
        $this->startSession();
        unset($_SESSION['user']);
        session_destroy();
    }
}