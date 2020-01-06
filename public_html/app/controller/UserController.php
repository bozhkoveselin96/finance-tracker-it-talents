<?php

namespace controller;

use model\users\User;
use model\users\UserDAO;
use PHPMailer\PHPMailer\PHPMailer;

class UserController
{
    public function checkLogin() {
        $response = [];
        $status = STATUS_FORBIDDEN;
        if ($_SERVER['REQUEST_METHOD'] == "POST") {
            if (isset($_SESSION['logged_user'])) {
                $response['id'] = $_SESSION['logged_user'];
                $response["first_name"] = $_SESSION['logged_user_first_name'];
                $response["last_name"] = $_SESSION['logged_user_last_name'];
                $response["avatar_url"] = $_SESSION['logged_user_avatar_url'];
                if ($_SESSION['logged_user_avatar_url'] == null) {
                    $response['avatar_url'] = NO_AVATAR_URL;
                }
                $status = STATUS_OK;
            }
        }
        header($status);
        return $response;
    }

    public function login() {
        $response = [];
        $status = STATUS_FORBIDDEN . 'Email and password mismatch.';
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
                    if ($user->avatar_url == null) {
                        $response['avatar_url'] = NO_AVATAR_URL;
                    }

                    $response["target"] = 'login';
                    $status = STATUS_OK;
                    UserDAO::updateLastLogin($_SESSION["logged_user"]);
                }
            }
        }
        header($status);
        return $response;
    }

    public function register() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'Something is not filled correctly.';
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
        $status = STATUS_BAD_REQUEST . 'Something is not filled correctly or you are not logged in.';

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
                $response['password_edited'] = true;
            } else {
                $response['password_edited'] = false;
                $editedUser->setPassword($user->password);
            }
            if (Validator::validateName($editedUser->getFirstName()) &&
                Validator::validateName($editedUser->getLastName()) &&
                UserDAO::edit($editedUser)) {
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
        }
        header($status);
        return $response;
    }

    public function logout() {
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

//new methods
    public function setNewPassword() {
        $status = STATUS_BAD_REQUEST . 'Something is not filled correctly or you are not logged in.';

        if (isset($_POST["change"]) && isset($_SESSION["logged_user"])) {
            if (Validator::validatePassword($_POST["password"]) && strcmp($_POST["password"], $_POST["rpassword"]) == 0) {
                $cryptedPass = password_hash($_POST["password"], PASSWORD_BCRYPT);
                $changed = UserDAO::changeForgottenPassword($cryptedPass, $_SESSION["logged_user"]);
                if ($changed) {
                    $status = STATUS_OK;
                }
            }
        }
        return header($status);
    }
    public function sendEmail() {
        if (isset($_POST["email"]) && UserDAO::getUser($_POST["email"])) {
            $email = $_POST["email"];
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
            $mail->Body =  'Hello, click <a href="localhost/finance_tracker/app/index.php?target=user&action=changePass&token='.$token.'">here</a> to change your password';

            if ($mail->send()) {
                $response = "Email is sent!";
                UserDAO::addToken($token, $_SESSION["logged_user"]);
            } else {
                $response = "Something is wrong: <br>" . $mail->ErrorInfo;
            }
            exit(json_encode(array("response" => $response)));
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
        if (!UserDAO::tokenExists($randomToken)) {
            return $randomToken; //turn the array into a string
        } else {
            return false;
        }
    }
}