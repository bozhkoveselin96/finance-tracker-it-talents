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
            $amount = $_POST['amount'];
            $note = $_POST['note'];
            $time_event = $_POST['time_event'];
            if ($account && $account->owner_id == $_SESSION['logged_user'] && $category &&
                ($category->owner_id == $_SESSION['logged_user'] || $category->owner_id == null) ) {
                $transaction = new Transaction($amount, $account->id, $category->id, $note, $time_event);
                if (TransactionDAO::create($transaction)) {
                    $response['status'] = true;
                }
            }
        }
        return $response;
    }
}