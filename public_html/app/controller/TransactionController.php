<?php


namespace controller;


use model\accounts\AccountDAO;
use model\categories\CategoryDAO;
use model\transactions\Transaction;
use model\transactions\TransactionDAO;

class TransactionController {
    public function add() {
        $response = [];
        $response['status'] = false;
        if (isset($_POST['add_transaction']) && isset($_SESSION['logged_user'])) {
            $account = AccountDAO::getAccountById($_POST['account_id']);
            $category = CategoryDAO::getCategoryById($_POST['category_id'], $account->owner_id);
            $transaction_type = $category->type;
            $amount = $_POST['amount'];
            $note = $_POST['note'];
            $time_event = $_POST['time_event'];

            if ($account && $account->owner_id == $_SESSION['logged_user'] && $category &&
                ($category->owner_id == $_SESSION['logged_user'] || $category->owner_id == null) ) {
                $transaction = new Transaction($amount, $account->id, $category->id, $note, $time_event);
                if (TransactionDAO::create($transaction, $transaction_type)) {
                    $response['target'] = 'transaction';
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }

    public function showUserTransactions() {
        $response = [];
        $response["status"] = false;

        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_SESSION["logged_user"]) && isset($_GET["user_id"])) {
            $user_id = $_GET["user_id"];
            $category_id = null;
            if (isset($_GET["category_id"])) {
                $category_id = $_GET["category_id"];
            }
            if ($_SESSION["logged_user"] = $user_id) {
                $transactions = TransactionDAO::getByUserAndCategory($user_id, $category_id);
                if ($transactions) {
                    $response["status"] = true;
                    $response["data"] = $transactions;
                }
            }
        }
        return $response;
    }
}