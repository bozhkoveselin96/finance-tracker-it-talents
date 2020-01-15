<?php

namespace controller;

require_once "PHPMailer/PHPMailer.php";
require_once "PHPMailer/SMTP.php";
require_once "PHPMailer/Exception.php";
require_once "gmail_config.php";

use exceptions\BadRequestException;
use exceptions\ForbiddenException;
use exceptions\MethodNotAllowedException;
use exceptions\NotFoundException;
use exceptions\UnauthorizedException;
use interfaces\Deletable;
use interfaces\Editable;
use model\users\User;
use model\users\UserDAO;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\PHPMailer;

class UserController implements Editable, Deletable {
    public function login() {
        if (isset($_POST["login"])) {
            if (!isset($_POST["email"]) || !Validator::validateEmail($_POST["email"])) {
                throw new BadRequestException("Not a valid email");
            } elseif (!isset($_POST["password"]) || !Validator::validatePassword($_POST["password"])) {
                throw new BadRequestException("Not a valid password");
            }

            $userDAO = new UserDAO();
            $user = $userDAO->getUser($_POST["email"]);

            if (!$user || !password_verify($_POST["password"], $user->getPassword())) {
                throw new UnauthorizedException('Email and/or password missmatch.');
            }

            $_SESSION["logged_user"] = $user->getId();

            if ($user->getAvatarUrl() == null) {
                $user->setAvatarUrl(NO_AVATAR_URL);
            }
            $userDAO->updateLastLogin($user->getId());

            return new ResponseBody("Login successful!", $user);
        }
        throw new MethodNotAllowedException("Method Not Allowed.");
    }

    public function register() {
        if (isset($_POST["register"])) {
            if (!isset($_POST["email"]) || !Validator::validateEmail($_POST['email'])) {
                throw new BadRequestException("Not a valid email");
            } elseif (!isset($_POST["password"]) || !isset($_POST["rpassword"]) || !Validator::validatePassword($_POST['password'])) {
                throw new BadRequestException(PASSWORD_WRONG_PATTERN_MESSAGE);
            } elseif (strcmp($_POST['password'], $_POST["rpassword"]) != 0) {
                throw new BadRequestException("Passwords missmatch.");
            }elseif (!isset($_POST["first_name"]) || !Validator::validateName($_POST['first_name'])) {
                throw new BadRequestException("First name must be have between " . MIN_LENGTH_NAME . " and ". MAX_LENGTH_NAME . " symbols inclusive");
            } elseif (!isset($_POST["last_name"]) || !Validator::validateName($_POST['last_name'])) {
                throw new BadRequestException("Last name must be have between " . MIN_LENGTH_NAME . " and ". MAX_LENGTH_NAME . " symbols inclusive");
            }

            $userDAO = new UserDAO();
            if ($userDAO->getUser($_POST['email'])) {
                throw new ForbiddenException("This email is already registered.");
            }

            $avatar_url = $this->uploadAvatar($_POST["email"]);
            $user = new User($_POST["email"], $_POST['password'], $_POST['first_name'], $_POST['last_name'], $avatar_url);

            $cryptedPass = password_hash($user->getPassword(), PASSWORD_BCRYPT);
            $user->setPassword($cryptedPass);
            $userDAO->register($user);
            return new ResponseBody("Register successful!", $user);
        }
        throw new MethodNotAllowedException("Method Not Allowed.");
    }

    public function edit() {
        if (isset($_POST["edit"])) {
            if (!isset($_POST["first_name"]) || !Validator::validateName($_POST['first_name'])) {
                throw new BadRequestException("First name must be have between " . MIN_LENGTH_NAME . " and ". MAX_LENGTH_NAME . " symbols inclusive");
            } elseif (!isset($_POST["last_name"]) || !Validator::validateName($_POST['last_name'])) {
                throw new BadRequestException("Last name must be have between " . MIN_LENGTH_NAME . " and ". MAX_LENGTH_NAME . " symbols inclusive");
            }

            $userDAO = new UserDAO();
            $user = $userDAO->getUser(intval($_SESSION["logged_user"]));
            $previousAvatar = $user->getAvatarUrl();
            $avatar_url = $this->uploadAvatar($user->getEmail());
            if ($avatar_url) {
                if (file_exists($previousAvatar)) {
                    unlink($previousAvatar);
                }
            } else {
                $avatar_url = $previousAvatar;
            }
            $user->setAvatarUrl($avatar_url);
            $user->setFirstName($_POST['first_name']);
            $user->setLastName($_POST['last_name']);

            $isEditedPass = "The password is not changed.";
            if (isset($_POST["password"]) && isset($_POST["rpassword"]) &&
                Validator::validatePassword($_POST["password"]) &&
                strcmp($_POST["password"], $_POST["rpassword"]) == 0) {
                    $cryptedPass = password_hash($_POST["password"], PASSWORD_BCRYPT);
                    $user->setPassword($cryptedPass);
                    $isEditedPass = "The password is changed.";
            }

            $userDAO->edit($user);
            if ($user->getAvatarUrl() == null) {
                $user->setAvatarUrl(NO_AVATAR_URL);
            }
            return new ResponseBody("Your profile edited successfully. " . $isEditedPass, $user);
        }
        throw new MethodNotAllowedException("Method Not Allowed.");
    }

    public function logout() {
        if (isset($_SESSION['logged_user'])) {
            unset($_SESSION["logged_user"]);
            return new ResponseBody("You successfully logged out.", null);
        }
        throw new MethodNotAllowedException('You are not logged in to logout!');
    }

    public function  delete() {
        if (isset($_POST["delete"])) {
            $userDAO = new UserDAO();
            $user = $userDAO->getUser(intval($_SESSION["logged_user"]));

            $userDAO->deleteProfile($user->getId());
            return new ResponseBody("Your profile deleted successfully.", $user);
        }
        throw new MethodNotAllowedException("Method Not Allowed.");
    }

    private function uploadAvatar($email) {
        $tempName = $_FILES["avatar"]["tmp_name"];

        if (is_uploaded_file($tempName)) {
            if ($_FILES['avatar']['size'] > IMAGE_MAX_UPLOAD_SIZE) {
                throw new BadRequestException("Max image size is 2MB!");
            } elseif (!Validator::validateMimeType(mime_content_type($tempName))) {
                throw new BadRequestException("Supported images are jpeg, png and gif!");
            }
            $ext = strtolower(pathinfo($_FILES["avatar"]["name"], PATHINFO_EXTENSION));
            $fileUrl = "avatars" . DIRECTORY_SEPARATOR . "$email-" . time() . ".$ext";
            if (move_uploaded_file($tempName, $fileUrl)) {
                return $fileUrl;
            }
        }
        return false;
    }

    public function setNewPassword() {
        if (isset($_POST["change"])) {
            if (!isset($_POST["token"]) || empty($_POST['token'])){
                throw new BadRequestException("No token for the password change.");
            } elseif (!isset($_POST["password"]) || !isset($_POST["rpassword"]) || !Validator::validatePassword($_POST["password"])) {
                throw new BadRequestException(PASSWORD_WRONG_PATTERN_MESSAGE);
            } elseif (strcmp($_POST["password"], $_POST["rpassword"]) != 0) {
                throw new BadRequestException("Passwords do not match!");
            }

            $userDAO = new UserDAO();
            $token = $userDAO->tokenExists($_POST["token"], true);
            if (!$token) {
                throw new NotFoundException("You did not want to change password or token has expired!");
            }

            $user = $userDAO->getUser(intval($token->owner_id));
            $cryptedPass = password_hash($_POST["password"], PASSWORD_BCRYPT);
            $user->setPassword($cryptedPass);
            $userDAO->changeForgottenPassword($user);
            return new ResponseBody("You changed your password successfully.", $user);
        }
        throw new MethodNotAllowedException("Method Not Allowed.");
    }

    public function sendEmail() {
        if (isset($_POST['changePassword'])) {
            if (!isset($_POST["email"]) || !Validator::validateEmail($_POST["email"])) {
                throw new BadRequestException("Not a valid email!");
            }
            $userDAO = new UserDAO();
            $user = $userDAO->getUser($_POST["email"]);
            if (!$user) {
                throw new NotFoundException("Not found a user with that email!");
            }

            $checkTokenExists = $userDAO->tokenExists($user->getId());
            if ($checkTokenExists) {
                $token = $checkTokenExists->token;
            } else {
                $token = $this->generateRandomToken();
                $userDAO->addToken($token, $user->getId());
            }

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
                return new ResponseBody("Email has been sent!", null);
            }
            throw new Exception($mail->ErrorInfo);
        }
        throw new MethodNotAllowedException("Method Not Allowed.");
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

    public function sendNotificationsToUnactiveUsers() {
        $userDAO = new UserDAO();
        $notActiveUsers = $userDAO->getLastTransaction();
        $today = date("Y-m-d");
        foreach ($notActiveUsers as $notActiveUser) {
            if (strtotime($notActiveUser->last_day) < strtotime($today) - MAX_DAYS_NOT_ACTIVE &&
                (strtotime($notActiveUser->last_time_sent_email) < strtotime($today) - MAX_DAYS_NOT_ACTIVE ||
                    $notActiveUser->last_time_sent_email == null)) {
                $this->sendNotificationEmail($notActiveUser->email);
                $userDAO->updateLastTimeSentEmail($notActiveUser->email);
            }
        }
    }

    private function sendNotificationEmail($email) {
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
        $mail->Subject = 'Not active user - Finance Tracker';
        $mail->addAddress($email);
        $mail->Body = '
            <html>
                <head>
                <title>Title</title>
                </head>
                <body>' .
            'Hello, you were not active from along time. Visit us at <a href="http://localhost/finance_tracker/public_html">Finance tracker</a>'
            . '</body>
            </html>
            ';
        $mail->isHTML(true);
        $mail->send();
    }
}