<?php


namespace controller;


use model\StatisticDAO;

class StatisticController {
    public function getIncomesOutcomes() {
        $status = STATUS_BAD_REQUEST . 'You do not have transactions or you are not logged in.';
        $response = [];
        if (isset($_SESSION['logged_user'])) {
            $incomes = StatisticDAO::getTransactionsSum($_SESSION['logged_user'], 1);
            $outcomes = StatisticDAO::getTransactionsSum($_SESSION['logged_user'], 0);
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
            $outcomes = StatisticDAO::getTransactionsByCategory($_SESSION['logged_user'], 0);
            if ($outcomes) {
                $status = STATUS_OK;
            }
        }
        header($status);
        return $outcomes;
    }

    public function getIncomesByCategory() {
        $status = STATUS_BAD_REQUEST . 'You do not have transactions or you are not logged in.';
        $response = [];
        if (isset($_SESSION['logged_user'])) {
            $incomes = StatisticDAO::getTransactionsByCategory($_SESSION['logged_user'], 1);
            if ($incomes) {
                $status = STATUS_OK;
            }
        }
        header($status);
        return $incomes;
    }
}
