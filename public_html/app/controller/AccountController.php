<?php


namespace controller;


use model\accounts\Account;
use model\accounts\AccountDAO;

class AccountController {
    public function add() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'Something is not filled correctly';
        if (isset($_POST['add_account'])) {
            $account = new Account($_POST['name'], $_POST['current_amount'], $_SESSION['logged_user']);
            $accountDAO = new AccountDAO();
            if (Validator::validateName($account->getName()) && Validator::validateAmount($account->getCurrentAmount())) {
                try {
                    $accountDAO->createAccount($account);
                    $response['target'] = 'addaccount';
                    $status = STATUS_CREATED;
                } catch (\Exception $exception) {
                    $status = STATUS_ACCEPTED . 'Not created. Please try again';
                }
            }
        }
        header($status);
        return $response;
    }

    public function getAll() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'Wrong parameters.';
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            try {
                $accountsDAO = new AccountDAO();
                $accounts = $accountsDAO->getMyAccounts($_SESSION['logged_user']);
                $status = STATUS_OK;
                $response['data'] = $accounts;
            } catch (\Exception $exception) {
                $status = STATUS_ACCEPTED . 'Something went wrong. Please try again.';
            }
        }
        header($status);
        return $response;
    }

    public function edit() {
        $status = STATUS_FORBIDDEN . 'Something is not filled correctly. Please try again.';

        if (isset($_POST["edit"]) && Validator::validateName($_POST["name"])) {
            try {
                $account_id = $_POST["account_id"];
                $accountDAO = new AccountDAO();

                //** @Account $account */
                $account = $accountDAO->getAccountById($account_id);
                $newAccountName = new Account($_POST["name"], $account->getCurrentAmount(), $account->getOwnerId());
                $newAccountName->setId($account->getId());
                if ($newAccountName->getOwnerId() == $account->getOwnerId()) {
                    $accountDAO->editAccount($newAccountName);
                    $status = STATUS_OK;
                }
            } catch (\Exception $exception) {
                $status = STATUS_ACCEPTED . 'Something went wrong. Please try again.';
            }
        }
        return header($status);
    }

    public function delete(){
        $status = STATUS_FORBIDDEN . 'You do not have access to this!';
        if (isset($_POST["delete"])) {
            try {
                $account_id = $_POST["account_id"];
                $accountDAO = new AccountDAO();
                $account = $accountDAO->getAccountById($account_id);

                if ($account->getOwnerId() == $_SESSION['logged_user']) {
                    $accountDAO->deleteAccount($account->getId());
                    $status = STATUS_OK;
                }
            } catch (\Exception $exception) {
                $status = STATUS_ACCEPTED . 'Something went wrong. Please try again.';
            }
        }
        return header($status);
    }
}