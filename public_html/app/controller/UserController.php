<?php

namespace controller;

use model\users\User;
use model\users\UserDAO;

class UserController {
    public function login(){
        $response = [];
        $response["status"] = false;
        if (isset($_POST["login"])) {
            $email = $_POST["email"];
            $password = $_POST["password"];
            if (!empty($email) && !empty($password) &&
                mb_strlen($password) >= MIN_LENGTH_PASSWORD &&
                filter_var($email , FILTER_VALIDATE_EMAIL)) {

                $user = UserDAO::getByEmail($email);

                if ($user && password_verify($password, $user->password)) {
                    $_SESSION["logged_user"] = $user->id;
                    $response["status"] = true;
                    $response['id'] = $user->id;
                    $response["full_name"] = $user->full_name;
                    $response["target"] = 'login';
                }
            }
        }
        return $response;
    }

    public function register() {
        $response = [];
        $response["status"] = false;
        if (isset($_POST["register"])) {
            $response["target"] = 'register';
            $user = new User($_POST["email"], $_POST['password'], $_POST['first_name'], $_POST['last_name']);

            if (!empty($user->getEmail()) &&
                !UserDAO::getByEmail($user->getEmail()) &&
                !empty($user->getPassword()) &&
                mb_strlen($user->getPassword()) >= MIN_LENGTH_PASSWORD &&
                filter_var($user->getEmail(),FILTER_VALIDATE_EMAIL) &&
                !empty($user->getFirstName()) &&
                !empty($user->getLastName()) &&
                mb_strlen($user->getFirstName()) > MIN_LENGTH_NAME &&
                mb_strlen($user->getLastName()) > MIN_LENGTH_NAME &&
                strcmp($user->getPassword(), $_POST["rpassword"]) == 0) {
                $cryptedPass = password_hash($user->getPassword(),PASSWORD_BCRYPT);
                $user->setPassword($cryptedPass);

                if (UserDAO::register($user)) {
                    $response["status"] = true;
                }
            }
        }
        return $response;
    }
}