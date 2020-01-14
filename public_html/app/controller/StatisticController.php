<?php


namespace controller;


use exceptions\BadRequestException;
use exceptions\NotFoundException;
use model\accounts\AccountDAO;
use model\CurrencyDAO;
use model\StatisticDAO;
use model\transactions\Transaction;

class StatisticController {
    public function getIncomesOutcomes() {
        if (!isset($_GET["currency"]) || !Validator::validateCurrency($_GET['currency'])) {
            throw new BadRequestException(MSG_SUPPORTED_CURRENCIES);
        }
        $statisticDAO = new StatisticDAO();
        $account = null;
        if (isset($_GET['account_id']) && $_GET['account_id'] > 0) {
            $accountDAO = new AccountDAO();
            $account = $accountDAO->getAccountById($_GET['account_id']);
            if (!$account || $account->getOwnerId() != $_SESSION['logged_user']) {
                throw new BadRequestException('Not a valid account.');
            }
        }

        if (isset($_GET["daterange"]) && !empty($_GET['daterange'])) {
            $daterange = explode(" - ", $_GET['daterange']);
            if (count($daterange) != 2) {
                throw new BadRequestException("Please select valid daterange.");
            }
            $from_date = date_format(date_create($daterange[0]), "Y-m-d");
            $to_date = date_format(date_create($daterange[1]), "Y-m-d");
            if (!Validator::validateDate($from_date) || !Validator::validateDate($to_date)) {
                throw new BadRequestException("Not valid dates.");
            }
            $transactions = $statisticDAO->getTransactionsSum($_SESSION['logged_user'], $account, $from_date, $to_date);
        } else {
            $transactions = $statisticDAO->getTransactionsSum($_SESSION['logged_user'], $account);
        }

        $currencyDAO = new CurrencyDAO();
        $response = new \stdClass();
        $response->outcomeSum = 0;
        $response->incomeSum = 0;
        $response->currency = $_GET['currency'];

        /** @var Transaction $transaction */
        foreach ($transactions as $transaction) {
            $amount = $transaction->getAmount();
            if ($transaction->getCurrency() != $response->currency) {
                $amount = $currencyDAO->currencyConverter($transaction->getAmount(), $transaction->getCurrency(), $response->currency);
            }

            if ($transaction->getCategory()->getType() == CATEGORY_OUTCOME) {
                $response->outcomeSum += $amount;
            } else {
                $response->incomeSum += $amount;
            }
        }
        $response->outcomeSum = round($response->outcomeSum, 2);
        $response->incomeSum = round($response->incomeSum, 2);
        return new ResponseBody(null, $response);
    }

    public function getSumByCategory() {
        $account = null;
        if (isset($_GET['account_id']) && !empty($_GET['account_id'])) {
            $accountDAO = new AccountDAO();
            $account = $accountDAO->getAccountById($_GET['account_id']);
            if (!$account || $account->getOwnerId() != $_SESSION['logged_user']) {
                $account = null;
            }
        }

        if (!isset($_GET['category_type']) || !Validator::validateCategoryType($_GET['category_type'])) {
            throw new BadRequestException("Not a valid category type.");
        }

        if (!isset($_GET['currency']) || !Validator::validateCurrency($_GET['currency'])) {
            throw new BadRequestException(MSG_SUPPORTED_CURRENCIES);
        }

        $from_date = null;
        $to_date = null;
        if (isset($_GET["daterange"]) && !empty($_GET['daterange'])) {
            $daterange = explode(" - ", $_GET['daterange']);
            if (count($daterange) != 2) {
                throw new BadRequestException("Please select valid daterange.");
            }
            $from_date = date_format(date_create($daterange[0]), "Y-m-d");
            $to_date = date_format(date_create($daterange[1]), "Y-m-d");
            if (!Validator::validateDate($from_date) || !Validator::validateDate($to_date)) {
                throw new BadRequestException("Not valid dates.");
            }
        }
        $statisticDAO = new StatisticDAO();
        $currencyDAO = new CurrencyDAO();
        $transactionsByCategory = $statisticDAO->getTransactionsByCategory($_SESSION['logged_user'], $categoryType, $account, $from_date, $to_date);
        $response = [];
        /** @var Transaction $transaction */
        foreach ($transactionsByCategory as $transaction) {
            $amount = $transaction->getAmount();
            if ($transaction->getCurrency() != $_GET['currency']) {
                $amount = $currencyDAO->currencyConverter($amount, $transaction->getCurrency(), $_GET['currency']);
            }

            if (!isset($response[$transaction->getCategory()->getName()])) {
                $response[$transaction->getCategory()->getName()] = ['amount'=>round($amount, 2), 'currency'=>$_GET['currency']];
            } else {
                $response[$transaction->getCategory()->getName()]['amount'] =
                    round($response[$transaction->getCategory()->getName()]['amount'] + $amount,2);
            }
        }

        return new ResponseBody($_GET['currency'], $response);
    }

    public function getDataForTheLastXDays() {
        if (!isset($_GET['days']) || $_GET['days'] < 0 || $_GET['days'] > 31) {
            throw new BadRequestException('Not valid days past.');
        } elseif (!isset($_GET["currency"]) || !Validator::validateCurrency($_GET["currency"])) {
            throw new BadRequestException(MSG_SUPPORTED_CURRENCIES);
        }
        $howManyDays = intval($_GET['days']);
        $currency = $_GET['currency'];
        $statisticDAO = new StatisticDAO();
        $transactions = $statisticDAO->getForTheLastXDays($_SESSION['logged_user'], $howManyDays);

        $days = [];
        for ($i = 0; $i < $howManyDays; $i++) {
            $date = date('j.m', strtotime('-'.$i.' days', time()));
            $days[$date] = ['outcome'=>0, 'income'=>0];
        }

        $currencyDAO = new CurrencyDAO();
        /** @var Transaction $transaction */
        foreach ($transactions as $transaction) {
            $amount = $transaction->getAmount();
            if ($transaction->getCurrency() != $currency) {
                $amount = $currencyDAO->currencyConverter($amount, $transaction->getCurrency(), $currency);
            }

            if ($transaction->getCategory()->getType() == 1) {
                $days[$transaction->getTimeEvent()]['income'] += $amount;
                $days[$transaction->getTimeEvent()]['income'] = round($days[$transaction->getTimeEvent()]['income'], 2);
            } else {
                $days[$transaction->getTimeEvent()]['outcome'] += $amount;
                $days[$transaction->getTimeEvent()]['outcome'] = round($days[$transaction->getTimeEvent()]['outcome'], 2);
            }
        }
        return new ResponseBody($currency, array_reverse($days));
    }
}
