<?php

namespace controller;

use exceptions\BadRequestException;
use exceptions\UnauthorizedException;
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
            if (!Validator::validateEmail($email)) {
                throw new BadRequestException("Not a valid email");
            } elseif (!Validator::validatePassword($password)) {
                throw new BadRequestException("Not a valid password");
            }

            $userDAO = new UserDAO();
            $user = $userDAO->getUser($email);

            if (!$user || !password_verify($password, $user->getPassword())) {
                throw new UnauthorizedException('Email and/or password missmatch.');
            }

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
        } else {
            throw new BadRequestException("Bad request.");
        }
        return $response;
    }

    public function register() {
        if (isset($_POST["register"])) {

            $avatar_url = $this->uploadAvatar($_POST["email"]);
            $user = new User($_POST["email"], $_POST['password'], $_POST['first_name'], $_POST['last_name'], $avatar_url);
            $userDAO = new UserDAO();
            $msg = null;
            if (!Validator::validateEmail($user->getEmail())) {
                $msg = "Not a valid email";
            } elseif ($userDAO->getUser($user->getEmail())) {
                $msg = "This email is already registered.";
            } elseif (!Validator::validatePassword($user->getPassword())) {
                $msg = PASSWORD_WRONG_PATTERN_MESSAGE;
            } elseif (strcmp($user->getPassword(), $_POST["rpassword"]) != 0) {
                $msg = "Passwords missmatch.";
            }elseif (!Validator::validateName($user->getFirstName())) {
                $msg = 'First name must have at least ' . MIN_LENGTH_NAME . ' letters.';
            } elseif (!Validator::validateName($user->getLastName())) {
                $msg = 'Last name must have at least ' . MIN_LENGTH_NAME . ' letters.';
            }

            if ($msg) {
                throw new BadRequestException($msg);
            }

            $cryptedPass = password_hash($user->getPassword(), PASSWORD_BCRYPT);
            $user->setPassword($cryptedPass);
            $userDAO->register($user);
        } else {
            throw new BadRequestException("Bad request");
        }
    }

    public function edit() {
        $response = [];
        if (isset($_POST["edit"]) && isset($_SESSION["logged_user"])) {

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

            if (!Validator::validateName($editedUser->getFirstName())) {
                throw new BadRequestException('First name must have at least ' . MIN_LENGTH_NAME . ' letters.');
            } elseif (!Validator::validateName($editedUser->getLastName())) {
                throw new BadRequestException('Last name must have at least ' . MIN_LENGTH_NAME . ' letters.');
            }

            if (Validator::validatePassword($editedUser->getPassword()) && strcmp($editedUser->getPassword(), $_POST["rpassword"]) == 0) {
                $cryptedPass = password_hash($editedUser->getPassword(), PASSWORD_BCRYPT);
                $editedUser->setPassword($cryptedPass);
                $response['password_edited'] = true;
            } else {
                $response['password_edited'] = false;
                $editedUser->setPassword($user->getPassword());
            }

            $userDAO->edit($editedUser);

            $response["first_name"] = $editedUser->getFirstName();
            $response["last_name"] = $editedUser->getLastName();
            $response["avatar_url"] = $editedUser->getAvatarUrl();
            if ($editedUser->getAvatarUrl() == null) {
                $response['avatar_url'] = NO_AVATAR_URL;
            }
            $_SESSION['logged_user_first_name'] = $editedUser->getFirstName();
            $_SESSION['logged_user_last_name'] = $editedUser->getLastName();
            $_SESSION['logged_user_avatar_url'] = $editedUser->getAvatarUrl();


        } else {
            throw new BadRequestException("Bad request");
        }
        return $response;
    }

    public function logout() {
        if (isset($_SESSION['logged_user'])) {
            unset($_SESSION["logged_user"]);
            unset($_SESSION["logged_user_first_name"]);
            unset($_SESSION["logged_user_last_name"]);
            unset($_SESSION['logged_user_avatar_url']);
        } else {
            throw new UnauthorizedException('You are not logged in to logout!');
        }
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