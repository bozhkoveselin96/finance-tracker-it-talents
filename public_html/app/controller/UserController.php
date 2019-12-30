<?php

namespace controller;

use model\users\User;
use model\users\UserDAO;
use const Grpc\STATUS_PERMISSION_DENIED;

class UserController {
    public function checkLogin() {
        $response = [];
        $status = STATUS_FORBIDDEN;
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (isset($_SESSION['logged_user']) && isset($_SESSION['logged_user_full_name'])) {
                $response['id'] = $_SESSION['logged_user'];
                $response["full_name"] = $_SESSION['logged_user_full_name'];
                $status = STATUS_OK;
            }
        }
        header($status);
        return $response;
    }

    public function login(){
        $response = [];
        $status = STATUS_FORBIDDEN;
        if (isset($_POST["login"])) {
            $email = $_POST["email"];
            $password = $_POST["password"];
            if (Validator::validateEmail($email) && Validator::validatePassword($password)) {
                $user = UserDAO::getByEmail($email);
                if ($user && password_verify($password, $user->password)) {
                    $_SESSION["logged_user"] = $user->id;
                    $_SESSION['logged_user_full_name'] = $user->full_name;
                    $response['id'] = $user->id;
                    $response["full_name"] = $user->full_name;
                    $response["target"] = 'login';
                    $status = STATUS_OK;
                }
            }
        }
        header($status);
        return $response;
    }

    public function register() {
        $response = [];
        $status = STATUS_BAD_REQUEST;
        if (isset($_POST["register"])) {
            $response["target"] = 'register';
            $user = new User($_POST["email"], $_POST['password'], $_POST['first_name'], $_POST['last_name']);

            if (Validator::validateEmail($user->getEmail()) &&
                !UserDAO::getByEmail($user->getEmail()) &&
                Validator::validatePassword($user->getPassword()) &&
                Validator::validateName($user->getFirstName()) &&
                Validator::validateName($user->getLastName()) &&
                strcmp($user->getPassword(), $_POST["rpassword"]) == 0) {
                $cryptedPass = password_hash($user->getPassword(),PASSWORD_BCRYPT);
                $user->setPassword($cryptedPass);

                if (UserDAO::register($user)) {
                    $status = STATUS_CREATED;
                }
            }
        }
        header($status);
        return $response;
    }

//    public function edit() {
//        $response = [];
//        $response["status"] = false;
//
//        if (isset($_POST["edit"])) {
//            $response["target"] = "edit";
//        }
//    }
}