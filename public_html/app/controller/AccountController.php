<?php


namespace controller;


use model\accounts\Account;
use model\accounts\AccountDAO;

class AccountController {
    public function add() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'Something is not filled correctly or you are not logged in.';
        if (isset($_POST['add_account']) && isset($_SESSION['logged_user'])) {
            $account = new Account($_POST['name'], $_POST['current_amount'], $_SESSION['logged_user']);
            if (Validator::validateName($account->getName()) && Validator::validateAmount($account->getCurrentAmount()) &&
                AccountDAO::createAccount($account)) {
                $response['target'] = 'addaccount';
                $status = STATUS_CREATED;
            }
        }
        header($status);
        return $response;
    }

    public function getAll() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'No accounts available or you are not logged in.';
        if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_SESSION['logged_user'])) {
            $accounts = AccountDAO::getMyAccounts($_SESSION['logged_user']);
            if ($accounts !== false) {
                $status = STATUS_OK;
                $response['data'] = $accounts;
            }

        }
        header($status);
        return $response;
    }

    public function edit() {
        $status = STATUS_FORBIDDEN . 'Something is not filled correctly or you are not logged in.';

        if (isset($_POST["edit"]) && isset($_SESSION['logged_user']) && Validator::validateName($_POST["name"])) {
            $account_id = $_POST["account_id"];
            $account = AccountDAO::getAccountById($account_id);
            if ($account) {
                $newAccountName = new Account($_POST["name"], $account->current_amount, $_SESSION['logged_user']);
                $newAccountName->setId($account_id);
                if ($newAccountName->getOwnerId() == $account->owner_id &&
                    AccountDAO::editAccount($newAccountName)) {
                    $status = STATUS_OK;
                }
            }
        }
        return header($status);
    }

    public function delete(){
        $status = STATUS_FORBIDDEN . 'You do not have access to this!';
        if (isset($_POST["delete"]) && isset($_SESSION['logged_user'])) {
            $user_id = $_SESSION['logged_user'];
            $account_id = $_POST["account_id"];
            $account = AccountDAO::getAccountById($account_id);

            if ($account && $user_id == $account->owner_id && AccountDAO::deleteAccount($account_id)) {
                $status = STATUS_OK;
            }
        }
        return header($status);
    }
}