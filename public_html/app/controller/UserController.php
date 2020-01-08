<?php

namespace controller;

use exceptions\BadRequestException;
use model\users\User;
use model\users\UserDAO;
use PHPMailer\PHPMailer\PHPMailer;

class UserController
{
    public function login() {
        $response = [];
        if (isset($_POST["login"])) {
            $email = $_POST["email"];
            $password = $_POST["password"];
            if (Validator::validateEmail($email) && Validator::validatePassword($password)) {
                try {
                    $userDAO = new UserDAO();
                    $user = $userDAO->getUser($email);
                    if ($user && password_verify($password, $user->getPassword())) {
                        $_SESSION["logged_user"] = $user->getId();
                        $_SESSION["logged_user_first_name"] = $user->getFirstName();
                        $_SESSION["logged_user_last_name"] = $user->getLastName();
                        $_SESSION["logged_user_avatar_url"] = $user->getAvatarUrl();

                        $response['id'] = $user->getId();
                        $response["first_name"] = $user->getFirstName();
                        $response["last_name"] = $user->getLastName();
                        $response["avatar_url"] = $user->getAvatarUrl();
                        if ($user->getAvatarUrl() == null) {
                            $response['avatar_url'] = NO_AVATAR_URL;
                        }

                        $response["target"] = 'login';
                        $userDAO->updateLastLogin($user->getId());
                    }
                } catch (\Exception $exception) {
                    $status = STATUS_ACCEPTED . 'Something went wrong. Please try again';
                }
            }
        }
        return $response;
    }

    public function register()
    {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'Something is not filled correctly.';
        if (isset($_POST["register"])) {
            try {
                $avatar_url = $this->uploadAvatar($_POST["email"]);
                $user = new User($_POST["email"], $_POST['password'], $_POST['first_name'], $_POST['last_name'], $avatar_url);
                $userDAO = new UserDAO();
                if (Validator::validateEmail($user->getEmail()) && !$userDAO->getUser($user->getEmail()) &&
                    Validator::validatePassword($user->getPassword()) &&
                    Validator::validateName($user->getFirstName()) &&
                    Validator::validateName($user->getLastName()) &&
                    strcmp($user->getPassword(), $_POST["rpassword"]) == 0) {

                    $cryptedPass = password_hash($user->getPassword(), PASSWORD_BCRYPT);
                    $user->setPassword($cryptedPass);
                    $userDAO->register($user);
                    $status = STATUS_OK;
                }
            } catch (\Exception $exception) {
                $status = STATUS_ACCEPTED . 'Something went wrong. Please try again';
            }
        }
        header($status);
        return $response;
    }

    public function edit()
    {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'Something is not filled correctly or you are not logged in.';

        if (isset($_POST["edit"]) && isset($_SESSION["logged_user"])) {
            try {
                $userDAO = new UserDAO();
                $user_id = $_SESSION["logged_user"];
                $user = $userDAO->getUser(intval($user_id));
                $oldAvatar = $user->getAvatarUrl();
                $avatar_url = $this->uploadAvatar($user->getEmail());
                if ($avatar_url) {
                    if (file_exists($oldAvatar)) {
                        unlink($oldAvatar);
                    }
                } else {
                    $avatar_url = $oldAvatar;
                }

                $editedUser = new User($user->getEmail(), $_POST["password"], $_POST["first_name"], $_POST["last_name"], $avatar_url);
                $editedUser->setId($user_id);

                if (Validator::validatePassword($editedUser->getPassword()) && strcmp($editedUser->getPassword(), $_POST["rpassword"]) == 0) {
                    $cryptedPass = password_hash($editedUser->getPassword(), PASSWORD_BCRYPT);
                    $editedUser->setPassword($cryptedPass);
                    $response['password_edited'] = true;
                } else {
                    $response['password_edited'] = false;
                    $editedUser->setPassword($user->getPassword());
                }
                if (Validator::validateName($editedUser->getFirstName()) &&
                    Validator::validateName($editedUser->getLastName())) {
                    $userDAO->edit($editedUser);
                    $status = STATUS_OK;
                    $response["first_name"] = $editedUser->getFirstName();
                    $response["last_name"] = $editedUser->getLastName();
                    $response["avatar_url"] = $editedUser->getAvatarUrl();
                    if ($editedUser->getAvatarUrl() == null) {
                        $response['avatar_url'] = NO_AVATAR_URL;
                    }
                    $_SESSION['logged_user_first_name'] = $editedUser->getFirstName();
                    $_SESSION['logged_user_last_name'] = $editedUser->getLastName();
                    $_SESSION['logged_user_avatar_url'] = $editedUser->getAvatarUrl();
                }
            } catch (\Exception $exception) {
                $status = STATUS_ACCEPTED . 'Something went wrong. Please try again';
            }
        }
        header($status);
        return $response;
    }

    public function logout()
    {
        $status = STATUS_FORBIDDEN;
        if (isset($_SESSION['logged_user'])) {
            unset($_SESSION["logged_user"]);
            unset($_SESSION["logged_user_first_name"]);
            unset($_SESSION["logged_user_last_name"]);
            unset($_SESSION['logged_user_avatar_url']);
            $status = STATUS_OK;
        }
        return header($status);
    }

    private function uploadAvatar($email)
    {
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

//new methods
//    public function setNewPassword() {
//        $status = STATUS_BAD_REQUEST . 'Something is not filled correctly';
//        if (isset($_POST["change"])) {
//            try {
//                if (Validator::validatePassword($_POST["password"]) && strcmp($_POST["password"], $_POST["rpassword"]) == 0) {
//                    $cryptedPass = password_hash($_POST["password"], PASSWORD_BCRYPT);
//                    $userDAO = new UserDAO();
//                    $user = $userDAO->getUser($_POST["email"]);
//                    $changed = new User($user->getEmail(), $user->getPassword(), $user->getFirstName(), $user->getLastName(), $user->getAvatarUrl());
//                    $changed->setId($user->getId());
//                    if ($changed->) {
//
//                    }
//                    $changed = $userDAO->changeForgottenPassword($cryptedPass, $user->getId());
//                    if ($changed) {
//                        $status = STATUS_OK;
//                    }
//                }
//            } catch (\PDOException $exception) {
//                $status = STATUS_ACCEPTED . 'Something went wrong. Please try again.';
//            }
//        }
//        return header($status);
//    }

    public function sendEmail() {
        $user_id = $_SESSION["logged_user"];
        $email = $_POST["email"];
        if (isset($email)) {
            try {
                $userDAO = new UserDAO();
                if ($userDAO->getUser($email)) {
                    $finance_tracker_email = "@gmail.com";
                    $finance_tracker_password = "";
                    $token = $this->generateRandomToken();
                    if (!$token) {
                        return false;
                    }

                    require_once "../PHPMailer/PHPMailer.php";
                    require_once "../PHPMailer/SMTP.php";
                    require_once "../PHPMailer/Exception.php";
                    $mail = new PHPMailer();

                    //SMTP Settings
                    $mail->isSMTP();
                    $mail->Host = "smtp.gmail.com";
                    $mail->SMTPAuth = true;
                    $mail->Username = $finance_tracker_email;
                    $mail->Password = $finance_tracker_password;
                    $mail->Port = 465; //587
                    $mail->SMTPSecure = "ssl"; //tls

                    //Email Settings
                    $mail->isHTML(true);
                    $mail->setFrom($finance_tracker_email, "Finance Tracker");
                    $mail->addAddress($email);
                    $mail->Body = 'Hello, click <a href="localhost/finance_tracker/app/index.php?target=user&action=changePass&token=' . $token . '">here</a> to change your password';

                    if ($mail->send()) {
                        $response = "Email is sent!";
                        $userDAO->addToken($token, $user_id);
                    } else {
                        $response = "Something is wrong: <br>" . $mail->ErrorInfo;
                    }
                    exit(json_encode(array("response" => $response)));
                }
            } catch (\PDOException $exception) {

            }
        }
    }

    private function generateRandomToken()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $token = array(); //remember to declare $pass as an array
        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
        for ($i = 0; $i < 10; $i++) {
            $n = rand(0, $alphaLength);
            $token[] = $alphabet[$n];
        }
        $randomToken = implode($token);
        try {
            $userDAO = new UserDAO();
            if (!$userDAO->tokenExists($randomToken)) {
                return $randomToken; //turn the array into a string
            } else {
                return false;
            }
        } catch (\PDOException $exception) {

        }
    }
}