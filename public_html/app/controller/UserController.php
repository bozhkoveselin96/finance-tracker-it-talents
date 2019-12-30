<?php

namespace controller;

use model\users\User;
use model\users\UserDAO;

class UserController
{
    public function checkLogin()
    {
        $response = [];
        $response['status'] = false;
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (isset($_SESSION['logged_user']) && isset($_SESSION['logged_user_full_name'])) {
                $response["status"] = true;
                $response['id'] = $_SESSION['logged_user'];
                $response["full_name"] = $_SESSION['logged_user_full_name'];
            }

        }
        return $response;
    }

    public function login()
    {
        $response = [];
        $response["status"] = false;
        if (isset($_POST["login"])) {
            $email = $_POST["email"];
            $password = $_POST["password"];
            if (Validator::validateEmail($email) && Validator::validatePassword($password)) {
                $user = UserDAO::getUser($email);
                if ($user && password_verify($password, $user->password)) {
                    $_SESSION["logged_user"] = $user->id;
                    $_SESSION['logged_user_full_name'] = $user->first_name . " " . $user->last_name;
                    $response["status"] = true;
                    $response['id'] = $user->id;
                    $response["first_name"] = $user->first_name;
                    $response["last_name"] = $user->last_name;
                    $response["avatar_url"] = $user->avatar_url;
                    $response["target"] = 'login';
                }
            }
        }
        return $response;
    }

    public function register()
    {
        $response = [];
        $response["status"] = false;
        if (isset($_POST["register"])) {
            $response["target"] = 'register';
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
                    $response["status"] = true;
                } else {
                    unlink($user->getAvatarUrl());
                }
            }
        }
        return $response;
    }

    public function edit()
    {
        $response = [];
        $response["status"] = false;
        $changedPass = false;

        if (isset($_POST["edit"]) && isset($_SESSION["logged_user"])) {
            $response["target"] = "edit";
            $user_id = $_SESSION["logged_user"];
            $user = UserDAO::getUser(intval($user_id));
            $oldAvatar = $user->avatar_url;
            $avatar_url = $this->uploadAvatar($user->getEmail());
            if ($avatar_url) {
                unlink($oldAvatar);
            } else {
                $avatar_url = $oldAvatar;
            }

            $editedUser = new User($user->getEmail(), $_POST["password"], $_POST["first_name"], $_POST["last_name"], $avatar_url);
            $editedUser->setId($user_id);

            if (Validator::validatePassword($editedUser->getPassword()) && strcmp($editedUser->getPassword(), $_POST["rpassword"]) == 0) {
                $changedPass = true;
            }

            if (Validator::validatePassword($editedUser->getPassword()) &&
                Validator::validateName($editedUser->getFirstName()) &&
                Validator::validateName($editedUser->getLastName())) {
                if ($changedPass) {
                    $cryptedPass = password_hash($editedUser->getPassword(), PASSWORD_BCRYPT);
                    $editedUser->setPassword($cryptedPass);
                } else {
                    $editedUser->setPassword($user->password);
                }

                if (UserDAO::edit($editedUser)) {
                    $response["status"] = true;
                    $response["first_name"] = $editedUser->getFirstName();
                    $response["last_name"] = $editedUser->getLastName();
                    $response["avatar_url"] = $editedUser->getAvatarUrl();
                }
            }
        }
        return $response;
    }

    public function uploadAvatar($email)
    {
        $fileUrl = null;
        $tempName = $_FILES["avatar"]["tmp_name"];
        $originalName = $_FILES["avatar"]["name"];
        $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));

        if (is_uploaded_file($tempName)) {
            $fileUrl = "avatars/$email-" . time() . ".$ext";
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