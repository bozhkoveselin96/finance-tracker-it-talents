<?php


namespace controller;


use model\accounts\Account;
use model\accounts\AccountDAO;

class AccountController {
    public function add() {
        $response = [];
        $response['status'] = false;
        if (isset($_POST['add_account']) && isset($_SESSION['logged_user'])) {
            $account = new Account($_POST['name'], $_POST['current_amount'], $_SESSION['logged_user']);

            if (mb_strlen($account->getName()) >= MIN_LENGTH_NAME && is_numeric($account->getCurrentAmount()) &&
                $account->getCurrentAmount() >= 0) {
                if (AccountDAO::createAccount($account)) {
                    $response['status'] = true;
                    $response['target'] = 'addaccount';
                }
            }
        }
        return $response;
    }

    public function getAll() {
        $response = [];
        $response['status'] = false;
        if ($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_SESSION['logged_user']) && isset($_GET['user_id'])) {
            $user_id = $_GET['user_id'];
            if ($user_id == $_SESSION['logged_user']) {
                $accounts = AccountDAO::getMyAccounts($user_id);
                if ($accounts) {
                    $response['status'] = true;
                    $response['data'] = $accounts;
                }
            }
        }
        return $response;
    }

    public function edit() {
        $response["status"] = false;
        if (isset($_POST["edit"])) {
            $account_id = $_POST["account_id"];
            $account = AccountDAO::getAccountById($account_id);
            if ($account) {
                $newAccountName = new Account($_POST["name"], $account->current_amount, $_POST["user_id"]);
                $newAccountName->setId($account_id);
                if ($newAccountName->getOwnerId() == $_SESSION["logged_user"] &&
                    $newAccountName->getOwnerId() == $account->owner_id &&
                    AccountDAO::editAccount($newAccountName)) {
                    $response["status"] = true;
                }
            }
        }
        return $response;
    }

    public function delete(){
        $response["status"] = false;
        if (isset($_POST["delete"])) {
            $user_id = $_POST["user_id"];
            $account_id = $_POST["account_id"];
            $account = AccountDAO::getAccountById($account_id);

            if ($account && $user_id && $user_id == $_SESSION["logged_user"] &&
                $user_id == $account->owner_id && AccountDAO::deleteAccount($account_id)) {
                $response["status"] = true;
            }
        }
        return $response;
    }
}