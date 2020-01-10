<?php


namespace controller;


use exceptions\BadRequestException;
use exceptions\ForbiddenException;
use model\accounts\AccountDAO;
use model\categories\CategoryDAO;
use model\transactions\Transaction;
use model\transactions\TransactionDAO;

class TransactionController {
    public function add() {
        if (isset($_POST['add_transaction']) && isset($_POST['account_id']) &&
            isset($_POST['category_id']) && !empty($_POST['time_event'])) {
            $accountDAO = new AccountDAO();
            $categoryDAO = new CategoryDAO();
            $account = $accountDAO->getAccountById($_POST['account_id']);
            $category = $categoryDAO->getCategoryById($_POST['category_id'], $account->getOwnerId());

            if (!$account) {
                throw new BadRequestException("No such account.");
            } elseif (!$category) {
                throw new BadRequestException("No such category.");
            }

            $transaction = new Transaction($_POST['amount'], $account, strtoupper($_POST['currency']), $category, $_POST['note'], $_POST['time_event']);

            if (!Validator::validateAmount($transaction->getAmount())) {
                throw new BadRequestException("Amount must be between 0 and " . MAX_AMOUNT . " inclusive!");
            } elseif (!Validator::validateDate($transaction->getTimeEvent())) {
                throw new BadRequestException("Please select valid day!");
            } elseif (!Validator::validateName($transaction->getNote())) {
                throw new BadRequestException("Name must be have between " . MIN_LENGTH_NAME . " and ". MAX_LENGTH_NAME . " symbols inclusive!");
            } elseif ($account->getOwnerId() != $_SESSION['logged_user']) {
                throw new ForbiddenException("This account is not yours.");
            } elseif (!Validator::validateCurrency($transaction->getCurrency())) {
                throw new BadRequestException(MSG_SUPPORTED_CURRENCIES);
            }

            $transactionDAO = new TransactionDAO();
            $id = $transactionDAO->create($transaction);
            $transaction->setId($id);
            return new ResponseBody('Transaction added successfully!', $transaction);
        }
        throw new BadRequestException("Bad request.");
    }

    public function showUserTransactions() {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $from_date = null;
            $to_date = null;
            if (!empty($_GET['date_range'])) {
                $date_range = explode(" - ", $_GET['date_range']);
                if (count($date_range) != 2) {
                    throw new BadRequestException("Please select valid date_range.");
                }
                $from_date = date_format(date_create($date_range[0]), "Y-m-d");
                $to_date = date_format(date_create($date_range[1]), "Y-m-d");
            }
            $category_id = isset($_GET["category_id"]) ? $_GET["category_id"] : null;
            $transactionDAO = new TransactionDAO();
            $transactions = $transactionDAO->getByUserAndCategory($_SESSION['logged_user'], $category_id, $from_date, $to_date);
            return new ResponseBody(null, $transactions);
        }
        throw new BadRequestException("Bad request.");
    }

    public function delete() {
        if ($_POST["delete"]) {
            $transaction_id = $_POST["transaction_id"];
            $transactionDAO = new TransactionDAO();
            $transaction = $transactionDAO->getTransactionById($transaction_id);

            if ($transaction->getAccount()->getOwnerId() == $_SESSION['logged_user']) {
                $transactionDAO->deleteTransaction($transaction);
            } else {
                throw new ForbiddenException("This transaction is not yours!");
            }
        }
        throw new BadRequestException("Bad request.");
    }
}