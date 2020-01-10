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
        if (is_numeric($amount) && $amount >= 0 && $amount < MAX_AMOUNT) {
            return true;
        }
        return false;
    }

    public static function validateDate($date) {
        if (empty($date)) {
            return false;
        }
        try {
            $checkDate = new \DateTime($date);
            return true;
        } catch (\Exception $exception) {
            return false;
        }
    }

    public static function validateCategoryType($type) {
        if ($type == CATEGORY_OUTCOME || $type == CATEGORY_INCOME) {
            return true;
        }
        return false;
    }

    public static function validateDayOfMonth($day) {
        if (is_numeric($day) && $day == intval($day) && $day > 0 && $day < 32) {
            return true;
        }

        return false;
    }

    public static function validateStatusPlannedPayment($status) {
        if ($status == 1 || $status == 0) {
            return true;
        }
        return false;
    }

    public static function validateCurrency($currency) {
        switch ($currency) {
            case "USD":
            case "EUR":
            case "BGN":
                return true;
        }
        return false;
    }
}