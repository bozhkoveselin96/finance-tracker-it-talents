<?php

namespace controller;

use exceptions\BadRequestException;
use exceptions\ForbiddenException;
use exceptions\NotFoundException;
use exceptions\UnauthorizedException;
use Interfaces\Deletable;
use Interfaces\Editable;
use model\users\User;
use model\users\UserDAO;
use PHPMailer\PHPMailer\PHPMailer;

class UserController implements Editable, Deletable {
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
            $user->setFirstName($_POST['first_name']);
            $user->setLastName($_POST['last_name']);
            if (!Validator::validateName($user->getFirstName())) {
                throw new BadRequestException("First name must be have between " . MIN_LENGTH_NAME . " and ". MAX_LENGTH_NAME . " symbols inclusive");
            } elseif (!Validator::validateName($user->getLastName())) {
                throw new BadRequestException("Last name must be have between " . MIN_LENGTH_NAME . " and ". MAX_LENGTH_NAME . " symbols inclusive");
            }

            $isEditedPass = " The password is not changed.";
            if (Validator::validatePassword($_POST["password"]) && strcmp($_POST["password"], $_POST["rpassword"]) == 0) {
                $cryptedPass = password_hash($_POST["password"], PASSWORD_BCRYPT);
                $user->setPassword($cryptedPass);
                $isEditedPass = " The password is changed.";
            }

            $userDAO->edit($user);
            if ($user->getAvatarUrl() == null) {
                $user->setAvatarUrl(NO_AVATAR_URL);
            }
            return new ResponseBody("Your profile edited successfully." . $isEditedPass, $user);
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

    public function setNewPassword() {
        if (isset($_POST["change"]) && $_POST["token"]) {
            if (!Validator::validatePassword($_POST["password"])) {
                throw new BadRequestException(PASSWORD_WRONG_PATTERN_MESSAGE);
            }elseif (strcmp($_POST["password"], $_POST["rpassword"]) != 0) {
                throw new BadRequestException("Passwords do not match!");
            }

            $userDAO = new UserDAO();
            $token = $userDAO->tokenExists($_POST["token"], true);
            if (!$token) {
                throw new NotFoundException("You did not want to change password!");
            }

            $user = $userDAO->getUser(intval($token->owner_id));
            $cryptedPass = password_hash($_POST["password"], PASSWORD_BCRYPT);
            $user->setPassword($cryptedPass);
            $userDAO->changeForgottenPassword($user);
            return new ResponseBody("You changed your password successfully.", $user);
        }
        throw new BadRequestException("Bad request");
    }

    public function sendEmail() {
        if (isset($_POST['changePassword'])) {
            $email = $_POST["email"];
            $userDAO = new UserDAO();

            if (!Validator::validateEmail($email)) {
                throw new BadRequestException("Not a valid email!");
            }
            $user = $userDAO->getUser($email);
            if (!$user) {
                throw new NotFoundException("Not found a user with that email!");
            }

            $checkTokenExists = $userDAO->tokenExists($user->getId());
            if ($checkTokenExists) {
                $token = $checkTokenExists->token;
            } else {
                $token = $this->generateRandomToken();
            }

            require_once "PHPMailer/PHPMailer.php";
            require_once "PHPMailer/SMTP.php";
            require_once "PHPMailer/Exception.php";
            require_once "gmail_config.php";
            $mail = new PHPMailer();
            //SMTP Settings
            $mail->isSMTP();
            $mail->Host = "smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Username = GMAIL_EMAIL;
            $mail->Password = GMAIL_PASSWORD;
            $mail->Port = 465; //587
            $mail->SMTPSecure = "ssl"; //tls
            $mail->CharSet = "utf-8";


            //Email Settings
            $mail->setFrom(GMAIL_EMAIL, "Finance Tracker");
            $mail->Subject = 'Forgotten password - Finance Tracker';
            $mail->addAddress($user->getEmail());
            $mail->Body = '
            <html>
                <head>
                <title>Title</title>
                </head>
                <body>' .
                    'Hello '.$user->getFirstName() . ' ' . $user->getLastName() . ',
                 <br> click <a href="http://localhost/finance_tracker/public_html/changeforgottenpass.html?token=' . $token . '">here</a>
                 to change your password.'
                . '</body>
            </html>
            ';
            $mail->isHTML(true);
            if ($mail->send()) {
                if (!$checkTokenExists) {
                    $userDAO->addToken($token, $user->getId());
                }
                return new ResponseBody("Email has been sent!", null);
            } else {
                throw new BadRequestException($mail->ErrorInfo);
            }
        }
        throw new BadRequestException("Bad request.");
    }

    private function generateRandomToken() {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
        $token = [];
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < TOKEN_LENGTH; $i++) {
            $n = rand(0, $alphaLength);
            $token[] = $alphabet[$n];
        }
        $randomToken = implode($token);
        return $randomToken;
    }
}