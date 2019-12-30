<?php

namespace controller;

use model\users\User;
use model\users\UserDAO;

class UserController
{
    public function checkLogin()
    {
        $response = [];
        $status = STATUS_FORBIDDEN;
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (isset($_SESSION['logged_user'])) {
                $response['id'] = $_SESSION['logged_user'];
                $response["first_name"] = $_SESSION['logged_user_first_name'];
                $response["last_name"] = $_SESSION['logged_user_last_name'];
                $response["avatar_url"] = $_SESSION['logged_user_avatar_url'];
                $status = STATUS_OK;
            }
        }
        header($status);
        return $response;
    }

    public function login() {
        $response = [];
        $status = STATUS_FORBIDDEN;
        if (isset($_POST["login"])) {
            $email = $_POST["email"];
            $password = $_POST["password"];
            if (Validator::validateEmail($email) && Validator::validatePassword($password)) {
                $user = UserDAO::getUser($email);
                if ($user && password_verify($password, $user->password)) {
                    $_SESSION["logged_user"] = $user->id;
                    $_SESSION["logged_user_first_name"] = $user->first_name;
                    $_SESSION["logged_user_last_name"] = $user->last_name;
                    $_SESSION["logged_user_avatar_url"] = $user->avatar_url;

                    $response['id'] = $user->id;
                    $response["first_name"] = $user->first_name;
                    $response["last_name"] = $user->last_name;
                    $response["avatar_url"] = $user->avatar_url;
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
            $avatar_url = $this->uploadAvatar($_POST["email"]);
            $user = new User($_POST["email"], $_POST['password'], $_POST['first_name'], $_POST['last_name'], $avatar_url);
            if (Validator::validateEmail($user->getEmail()) &&
                !UserDAO::getUser($user->getEmail()) &&
                Validator::validatePassword($user->getPassword()) &&
                Validator::validateName($user->getFirstName()) &&
                Validator::validateName($user->getLastName()) &&
                strcmp($user->getPassword(), $_POST["rpassword"]) == 0) {
                $cryptedPass = password_hash($user->getPassword(), PASSWORD_BCRYPT);
                $user->setPassword($cryptedPass);
                if (UserDAO::register($user)) {
                    $status = STATUS_OK;
                } else {
                    unlink($user->getAvatarUrl());
                }
            }
        }
        header($status);
        return $response;
    }

    public function edit() {
        $response = [];
        $status = STATUS_BAD_REQUEST;

        if (isset($_POST["edit"]) && isset($_SESSION["logged_user"])) {
            $user_id = $_SESSION["logged_user"];
            $user = UserDAO::getUser(intval($user_id));
            $oldAvatar = $user->avatar_url;
            $avatar_url = $this->uploadAvatar($user->email);
            if ($avatar_url) {
                if (file_exists($oldAvatar)) {
                    unlink($oldAvatar);
                }
            } else {
                $avatar_url = $oldAvatar;
            }

            $editedUser = new User($user->email, $_POST["password"], $_POST["first_name"], $_POST["last_name"], $avatar_url);
            $editedUser->setId($user_id);

            if (Validator::validatePassword($editedUser->getPassword()) && strcmp($editedUser->getPassword(), $_POST["rpassword"]) == 0) {
                $cryptedPass = password_hash($editedUser->getPassword(), PASSWORD_BCRYPT);
                $editedUser->setPassword($cryptedPass);
            } else {
                $editedUser->setPassword($user->password);
            }
            if (Validator::validateName($editedUser->getFirstName()) &&
                Validator::validateName($editedUser->getLastName()) &&
                UserDAO::edit($editedUser)) {
                    $status = STATUS_OK;
                    $response["first_name"] = $editedUser->getFirstName();
                    $response["last_name"] = $editedUser->getLastName();
                    $response["avatar_url"] = $editedUser->getAvatarUrl();
            }
        }
        header($status);
        return $response;
    }

    private function uploadAvatar($email) {
        $tempName = $_FILES["avatar"]["tmp_name"];
        $ext = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));

        if (is_uploaded_file($tempName)) {
            $fileUrl = "avatars" . DIRECTORY_SEPARATOR . "$email-" . time() . ".$ext";
            if (move_uploaded_file($tempName, $fileUrl)) {
                return $fileUrl;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
}