<?php

class UserModel extends Model {
    public function getAllUsers() {
        return $this->db->fetchAll("SELECT * FROM users");
    }

    public function getUserById($id) {
        return $this->db->fetchOne("SELECT * FROM users WHERE id = ?", [$id]);
    }

    public function addUser($name, $email, $password, $role = 'member') {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $token = bin2hex(random_bytes(32));
        $this->db->query(
            "INSERT INTO users (name, email, password, role, verification_token) VALUES (?, ?, ?, ?, ?)",
            [$name, $email, $hashedPassword, $role, $token]
        );
        return ['id' => $this->db->getConnection()->insert_id, 'token' => $token];
    }

    public function login($email, $password) {
        $user = $this->db->fetchOne("SELECT * FROM users WHERE email = ? AND verified = 1", [$email]);
        if ($user && password_verify($password, $user['password'])) {
            return $user;
        }
        return null;
    }

    public function verifyEmail($token) {
        $user = $this->db->fetchOne("SELECT * FROM users WHERE verification_token = ?", [$token]);
        if ($user) {
            $this->db->query("UPDATE users SET verified = 1, verification_token = NULL WHERE id = ?", [$user['id']]);
            return true;
        }
        return false;
    }

    public function emailExists($email) {
        return $this->db->fetchOne("SELECT id FROM users WHERE email = ?", [$email]) !== null;
    }

    // New methods for password reset
    public function createResetToken($email) {
        $user = $this->db->fetchOne("SELECT * FROM users WHERE email = ?", [$email]);
        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour')); // Token expires in 1 hour
            $this->db->query(
                "UPDATE users SET reset_token = ?, reset_expires = ? WHERE id = ?",
                [$token, $expires, $user['id']]
            );
            return $token;
        }
        return null;
    }

    public function verifyResetToken($token) {
        $user = $this->db->fetchOne(
            "SELECT * FROM users WHERE reset_token = ? AND reset_expires > NOW()",
            [$token]
        );
        return $user ? $user['id'] : null;
    }

    public function updatePassword($userId, $password) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
        $this->db->query(
            "UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE id = ?",
            [$hashedPassword, $userId]
        );
    }
}