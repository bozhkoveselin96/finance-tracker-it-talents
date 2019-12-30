<?php


namespace controller;


use model\accounts\Account;
use model\accounts\AccountDAO;

class AccountController {
    public function add() {
        $response = [];
        $status = STATUS_BAD_REQUEST;
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
        $status = STATUS_BAD_REQUEST;
        if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_SESSION['logged_user']) && isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
            if (Validator::validateLoggedUser($user_id)) {
                $accounts = AccountDAO::getMyAccounts($user_id);
                if ($accounts) {
                    $status = STATUS_OK;
                    $response['data'] = $accounts;
                }
            }
        }
        header($status);
        return $response;
    }

    public function edit() {
        $status = STATUS_FORBIDDEN;

        if (isset($_POST["edit"])) {
            $account_id = $_POST["account_id"];
            $account = AccountDAO::getAccountById($account_id);
            if ($account && Validator::validateLoggedUser($_POST["user_id"])) {
                $newAccountName = new Account($_POST["name"], $account->current_amount, $_POST["user_id"]);
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
        $status = STATUS_FORBIDDEN;
        if (isset($_POST["delete"])) {
            $user_id = $_POST["user_id"];
            $account_id = $_POST["account_id"];
            $account = AccountDAO::getAccountById($account_id);

            if ($account && $user_id && Validator::validateLoggedUser($user_id) &&
                $user_id == $account->owner_id && AccountDAO::deleteAccount($account_id)) {
                $status = STATUS_OK;
            }
        }
        return header($status);
    }
}