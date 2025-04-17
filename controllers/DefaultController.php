<?php

class DefaultController extends Controller {
    public function index() {
        $userModel = new UserModel();
        $users = $userModel->getAllUsers();
        $this->render('home', ['users' => $users], $this->sessionModel->isAdmin() ? 'admin' : 'base');
    }
}