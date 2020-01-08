<?php


namespace controller;


use exceptions\BadRequestException;
use exceptions\ForbiddenException;
use exceptions\NotFoundException;
use model\accounts\Account;
use model\accounts\AccountDAO;

class AccountController
{
    public function add()
    {
        $response = [];
        if (isset($_POST['add_account'])) {
            $account = new Account($_POST['name'], $_POST['current_amount'], $_SESSION['logged_user']);
            $accountDAO = new AccountDAO();
            if (!Validator::validateName($account->getName())) {
                throw new BadRequestException("Name must be have greater than " . MIN_LENGTH_NAME . " symbols");
            } elseif (!Validator::validateAmount($account->getCurrentAmount())) {
                throw new BadRequestException("Amount must be between 0 and " . MAX_AMOUNT . " inclusive");
            } else {
                $accountDAO->createAccount($account);
                $response['target'] = 'addaccount';
            }
        }
        return $response;
    }

    public function getAll() {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $accountsDAO = new AccountDAO();
            $accounts = $accountsDAO->getMyAccounts($_SESSION['logged_user']);
            return $accounts;
        }
    }

    public function edit() {
        if (isset($_POST["edit"])) {
            if (!Validator::validateName($_POST["name"])) {
                throw new BadRequestException("Name must be have greater than " . MIN_LENGTH_NAME . " symbols");
            }
            $account_id = $_POST["account_id"];
            $accountDAO = new AccountDAO();

            //** @Account $account */
            $account = $accountDAO->getAccountById($account_id);
            if (!$account) {
                throw new ForbiddenException("This account is not yours");
            }
            $newAccountName = new Account($_POST["name"], $account->getCurrentAmount(), $_SESSION['logged_user']);
            $newAccountName->setId($account->getId());
            if ($newAccountName->getOwnerId() == $account->getOwnerId()) {
                $accountDAO->editAccount($newAccountName);
            } else {
                throw new ForbiddenException("This account is not yours");
            }
        }
    }

    public function delete() {
        if (isset($_POST["delete"])) {
            $account_id = $_POST["account_id"];
            $accountDAO = new AccountDAO();
            $account = $accountDAO->getAccountById($account_id);

            if ($account->getOwnerId() == $_SESSION['logged_user']) {
                $accountDAO->deleteAccount($account->getId());
            } else {
                throw new ForbiddenException("This account is not yours");
            }
        }
    }
}