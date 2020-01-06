<?php


namespace controller;


use model\StatisticDAO;

class StatisticController {
    public function getIncomesOutcomes() {
        $status = STATUS_BAD_REQUEST . 'You do not have transactions or you are not logged in.';
        $response = [];
        if (isset($_SESSION['logged_user'])) {
            if (isset($_GET['from_date']) && Validator::validateDate($_GET['from_date']) &&
                isset($_GET['to_date']) && Validator::validateDate($_GET['to_date'])) {
                $from_date = $_GET['from_date'];
                $to_date = $_GET['to_date'];
                $incomes = StatisticDAO::getTrForSelectedPeriod($_SESSION['logged_user'], 1, $from_date, $to_date);
                $outcomes = StatisticDAO::getTrForSelectedPeriod($_SESSION['logged_user'], 0, $from_date, $to_date);
            } else {
                $incomes = StatisticDAO::getTransactionsSum($_SESSION['logged_user'], 1);
                $outcomes = StatisticDAO::getTransactionsSum($_SESSION['logged_user'], 0);
            }
            if ($incomes && $outcomes) {
                $response[] = $incomes;
                $response[] = $outcomes;
                $status = STATUS_OK;
            }
        }
        header($status);
        return $response;
    }

    public function getOutcomesByCategory() {
        $status = STATUS_BAD_REQUEST . 'You do not have transactions or you are not logged in.';
        $response = [];
        if (isset($_SESSION['logged_user'])) {
            if (isset($_GET['from_date']) && Validator::validateDate($_GET['from_date']) &&
                isset($_GET['to_date']) && Validator::validateDate($_GET['to_date'])) {
                $from_date = $_GET['from_date'];
                $to_date = $_GET['to_date'];
                $incomes = StatisticDAO::getTrForSelectedPeriodByCategories($_SESSION['logged_user'], 0, $from_date, $to_date);
            } else {
                $incomes = StatisticDAO::getTransactionsByCategory($_SESSION['logged_user'], 0);
            }

            if ($incomes) {
                $response = $incomes;
                $status = STATUS_OK;
            }
        }
        header($status);
        return $response;
    }

    public function getIncomesByCategory() {
        $status = STATUS_BAD_REQUEST . 'You do not have transactions or you are not logged in.';
        $response = [];
        if (isset($_SESSION['logged_user'])) {
            if (isset($_GET['from_date']) && Validator::validateDate($_GET['from_date']) &&
                isset($_GET['to_date']) && Validator::validateDate($_GET['to_date'])) {
                $from_date = $_GET['from_date'];
                $to_date = $_GET['to_date'];
                $incomes = StatisticDAO::getTrForSelectedPeriodByCategories($_SESSION['logged_user'], 1, $from_date, $to_date);
            } else {
                $incomes = StatisticDAO::getTransactionsByCategory($_SESSION['logged_user'], 1);
            }

            if ($incomes) {
                $response = $incomes;
                $status = STATUS_OK;
            }
        }
        header($status);
        return $response;
    }

    public function getDataForTheLastThirtyDays() {
        $status = STATUS_BAD_REQUEST . 'You do not have transactions or you are not logged in.';
        $response = [];
        if (isset($_SESSION['logged_user'])) {
            $user_id = $_SESSION["logged_user"];
            $data = StatisticDAO::getForTheLastThirtyDays($user_id);
            if ($data) {
                $response = $data;
                $status = STATUS_OK;
            }
        }
        header($status);
        return $response;
    }
}
