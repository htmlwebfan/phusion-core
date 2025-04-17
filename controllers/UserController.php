<?php

class UserController extends Controller {
    private $userModel;

    public function __construct() {
        parent::__construct();
        $this->userModel = new UserModel();
    }

    public function profile($id = null) {
        if ($id === null) {
            $this->render('user/profile', ['user' => null]);
        } else {
            $user = $this->userModel->getUserById($id);
            if ($user) {
                $this->render('user/profile', ['user' => $user]);
            } else {
                $this->render('user/profile', ['user' => null, 'error' => 'User not found']);
            }
        }
    }
}