<?php


namespace controller;


class Validator {
    public static function validateEmail($email) {
        if (!empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }
        return false;
    }

    public static function validatePassword($password) {
        if (!empty($password) && mb_strlen($password) >= MIN_LENGTH_PASSWORD &&
            preg_match(PASSWORD_PATTERN, $password)) {
            return true;
        }
        return false;
    }

    public static function validateName($name) {
        if (!empty($name) && mb_strlen($name) >= MIN_LENGTH_NAME) {
            return true;
        }
        return false;
    }

    public static function validateAmount($amount) {
        if (is_numeric($amount) && $amount >= 0) {
            return true;
        }
        return false;
    }

    public static function validateLoggedUser($user_id) {
        if ($user_id == $_SESSION['logged_user']) {
            return true;
        }
        return false;
    }
}