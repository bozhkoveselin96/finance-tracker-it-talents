<?php


namespace controller;


use model\accounts\AccountDAO;
use model\categories\CategoryDAO;
use model\transactions\Transaction;
use model\transactions\TransactionDAO;

class TransactionController {
    public function add() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'Something is not filled correctly';
        if (isset($_POST['add_transaction']) && isset($_POST['account_id']) && isset($_POST['category_id']) &&
             !empty($_POST['time_event'])) {
            try {
                $accountDAO = new AccountDAO();
                $categoryDAO = new CategoryDAO();
                $account = $accountDAO->getAccountById($_POST['account_id']);
                $category = $categoryDAO->getCategoryById($_POST['category_id'], $account->getOwnerId());
                $transaction = new Transaction($_POST['amount'], $account->getId(), $category->getId(), $_POST['note'], $_POST['time_event']);

                if ($account && $account->getOwnerId() == $_SESSION['logged_user'] && $category &&
                    Validator::validateAmount($transaction->getAmount()) && Validator::validateDate($transaction->getTimeEvent()) &&
                    Validator::validateName($transaction->getNote())) {
                    $transactionDAO = new TransactionDAO();
                    $transactionDAO->create($transaction, $category->getType());
                    $response['target'] = 'transaction';
                    $status = STATUS_CREATED;
                }
            } catch (\Exception $exception) {
                $status = STATUS_ACCEPTED . 'Not created. Please try again';
            }
        }
        header($status);
        return $response;
    }

    public function showUserTransactions() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'No transactions available or you are not logged in.';

        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            try {
                $category_id = isset($_GET["category_id"]) ? $_GET["category_id"] : null;

                $transactionDAO = new TransactionDAO();
                $transactions = $transactionDAO->getByUserAndCategory($_SESSION['logged_user'], $category_id);
                $status = STATUS_OK;
                $response["data"] = $transactions;
            } catch (\Exception $exception) {
                $status = STATUS_ACCEPTED . 'Something went wrong. Please try again';
            }
        }
        header($status);
        return $response;
    }
}