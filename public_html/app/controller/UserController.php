<?php

namespace controller;

use exceptions\BadRequestException;
use exceptions\ForbiddenException;
use exceptions\NotFoundException;
use exceptions\UnauthorizedException;
use model\users\User;
use model\users\UserDAO;
use PHPMailer\PHPMailer\PHPMailer;

class UserController {
    public function login() {
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

            if ($user->getAvatarUrl() == null) {
                $user->setAvatarUrl(NO_AVATAR_URL);
            }
            $userDAO->updateLastLogin($user->getId());
            return new ResponseBody("Login successful!", $user);
        }
        throw new BadRequestException("Bad request.");
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
                $msg = "First name must be have between " . MIN_LENGTH_NAME . " and ". MAX_LENGTH_NAME . " symbols inclusive";
            } elseif (!Validator::validateName($user->getLastName())) {
                $msg = "Last name must be have between " . MIN_LENGTH_NAME . " and ". MAX_LENGTH_NAME . " symbols inclusive";
            }
            if ($msg) {
                throw new BadRequestException($msg);
            }

            $cryptedPass = password_hash($user->getPassword(), PASSWORD_BCRYPT);
            $user->setPassword($cryptedPass);
            $user_id = $userDAO->register($user);
            $user->setId($user_id);
            return new ResponseBody("Register successful", $user);
        }
        throw new BadRequestException("Bad request");
    }

    public function edit() {
        if (isset($_POST["edit"])) {
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
            $user->setAvatarUrl($avatar_url);
            if (!Validator::validateName($_POST["first_name"])) {
                throw new BadRequestException("First name must be have between " . MIN_LENGTH_NAME . " and ". MAX_LENGTH_NAME . " symbols inclusive");
            } elseif (!Validator::validateName($_POST["last_name"])) {
                throw new BadRequestException("Last name must be have between " . MIN_LENGTH_NAME . " and ". MAX_LENGTH_NAME . " symbols inclusive");
            }

            if (Validator::validatePassword($_POST["password"]) && strcmp($_POST["password"], $_POST["rpassword"]) == 0) {
                $cryptedPass = password_hash($_POST["password"], PASSWORD_BCRYPT);
                $user->setPassword($cryptedPass);
            }

            $userDAO->edit($user);
            if ($user->getAvatarUrl() == null) {
                $user->setAvatarUrl(NO_AVATAR_URL);
            }
            return new ResponseBody("Your profile edited successfully.", $user);
        }
        throw new BadRequestException("Bad request");
    }

    public function logout() {
        if (isset($_SESSION['logged_user'])) {
            unset($_SESSION["logged_user"]);
            return new ResponseBody("You successfully logged out.", null);
        }
        throw new UnauthorizedException('You are not logged in to logout!');
    }

    public function  delete() {
        if (isset($_POST["delete"])) {
            $user_id = $_SESSION["logged_user"];
            $userDAO = new UserDAO();
            $user = $userDAO->getUser($user_id);

            if ($user) {
                $userDAO->deleteProfile($user->getId());
                return new ResponseBody("Your profile deleted successfully.", $user);
            } else {
                throw new ForbiddenException("This profile is not yours!");
            }
        }
        throw new BadRequestException("Bad request.");
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

//    public function sendEmail() {
//        $user_id = $_SESSION["logged_user"];
//        $email = $_POST["email"];
//        if (isset($email)) {
//            $userDAO = new UserDAO();
//            if ($userDAO->getUser($email)) {
//                $finance_tracker_email = "financetrackerproject@gmail.com";
//                $finance_tracker_password = "financetracker";
//                $token = $this->generateRandomToken();
//                if (!$token) {
//                    return false;
//                }
//
//                require_once "../PHPMailer/PHPMailer.php";
//                require_once "../PHPMailer/SMTP.php";
//                require_once "../PHPMailer/Exception.php";
//                $mail = new PHPMailer();
//
//                //SMTP Settings
//                $mail->isSMTP();
//                $mail->Host = "smtp.gmail.com";
//                $mail->SMTPAuth = true;
//                $mail->Username = $finance_tracker_email;
//                $mail->Password = $finance_tracker_password;
//                $mail->Port = 465; //587
//                $mail->SMTPSecure = "ssl"; //tls
//
//                //Email Settings
//                $mail->isHTML(true);
//                $mail->setFrom($finance_tracker_email, "Finance Tracker");
//                $mail->addAddress($email);
//                $mail->Body = 'Hello, click <a href="localhost/finance_tracker/app/index.php?target=user&action=changePass&token=' . $token . '">here</a> to change your password';
//
//                if ($mail->send()) {
//                    $response = "Email is sent!";
//                    $userDAO->addToken($token, $user_id);
//                } else {
//                    $response = "Something is wrong: <br>" . $mail->ErrorInfo;
//                }
//                exit(json_encode(array("response" => $response)));
//            }
//        }
//    }

//    private function generateRandomToken() {
//        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
//        $token = array(); //remember to declare $token as an array
//        $alphaLength = strlen($alphabet) - 1; //put the length -1 in cache
//        for ($i = 0; $i < 5; $i++) {
//            $n = rand(0, $alphaLength);
//            $token[] = $alphabet[$n];
//        }
//        $randomToken = implode($token);
//        $userDAO = new UserDAO();
//        if (!$userDAO->tokenExists($randomToken)) {
//            return $randomToken; //turn the array into a string
//        }
//        return $this->generateRandomToken();
//    }
}