<?php


namespace controller;


use exceptions\BadRequestException;
use model\accounts\AccountDAO;
use model\categories\CategoryDAO;
use model\transactions\Transaction;
use model\transactions\TransactionDAO;

class TransactionController {
    public function add() {
        $response = [];
        if (isset($_POST['add_transaction']) && isset($_POST['account_id']) &&
            isset($_POST['category_id']) && !empty($_POST['time_event'])) {
            $accountDAO = new AccountDAO();
            $categoryDAO = new CategoryDAO();
            $account = $accountDAO->getAccountById($_POST['account_id']);
            $category = $categoryDAO->getCategoryById($_POST['category_id'], $account->getOwnerId());
            $transaction = new Transaction($_POST['amount'], $account->getId(), $category->getId(), $_POST['note'], $_POST['time_event']);

            if (!Validator::validateAmount($transaction->getAmount())) {
                throw new BadRequestException("Amount must be between 0 and" . MAX_AMOUNT . "inclusive");
            } elseif (!Validator::validateDate($transaction->getTimeEvent())) {
                throw new BadRequestException("Please select valid day");
            } elseif (!Validator::validateName($transaction->getNote())) {
                throw new BadRequestException("Name must be have greater than " . MIN_LENGTH_NAME . " symbols");
            }
            if ($account && $account->getOwnerId() == $_SESSION['logged_user'] && $category) {
                $transactionDAO = new TransactionDAO();
                $transactionDAO->create($transaction, $category->getType());
                $response['target'] = 'transaction';
            }
        }
        return $response;
    }

    public function showUserTransactions() {
        $response = [];
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $category_id = isset($_GET["category_id"]) ? $_GET["category_id"] : null;
            $transactionDAO = new TransactionDAO();
            $transactions = $transactionDAO->getByUserAndCategory($_SESSION['logged_user'], $category_id);
            $response["data"] = $transactions;
        }
        return $response;
    }
}