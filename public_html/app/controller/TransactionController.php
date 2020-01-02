<?php


namespace controller;


use model\accounts\AccountDAO;
use model\categories\CategoryDAO;
use model\transactions\Transaction;
use model\transactions\TransactionDAO;

class TransactionController {
    public function add() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'Something is not filled correctly or you are not logged in.';
        if (isset($_POST['add_transaction']) && isset($_SESSION['logged_user']) && isset($_POST['account_id']) &&
            isset($_POST['category_id']) && Validator::validateName($_POST['note']) && !empty($_POST['time_event'])) {
            $account = AccountDAO::getAccountById($_POST['account_id']);
            $category = CategoryDAO::getCategoryById($_POST['category_id'], $account->owner_id);
            $transaction_type = $category->type;
            $amount = $_POST['amount'];
            $note = $_POST['note'];
            $time_event = $_POST['time_event'];

            if ($account && $account->owner_id == $_SESSION['logged_user'] && $category &&
                Validator::validateAmount($amount) && Validator::validateDate($time_event)) {
                $transaction = new Transaction($amount, $account->id, $category->id, $note, $time_event);
                if (TransactionDAO::create($transaction, $transaction_type)) {
                    $response['target'] = 'transaction';
                    $status = STATUS_CREATED;
                }
            }
        }
        header($status);
        return $response;
    }

    public function showUserTransactions() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'No categories available or you are not logged in.';

        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_SESSION["logged_user"])) {
            $category_id = null;
            if (isset($_GET["category_id"])) {
                $category_id = $_GET["category_id"];
            }

            $transactions = TransactionDAO::getByUserAndCategory($_SESSION['logged_user'], $category_id);
            if ($transactions) {
                $status = STATUS_OK;
                $response["data"] = $transactions;
            }

        }
        header($status);
        return $response;
    }
}