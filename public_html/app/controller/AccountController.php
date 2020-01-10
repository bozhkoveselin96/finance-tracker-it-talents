<?php


namespace controller;


use exceptions\BadRequestException;
use exceptions\ForbiddenException;
use model\accounts\Account;
use model\accounts\AccountDAO;

class AccountController {
    public function add() {
        if (isset($_POST['add_account'])) {
            $account = new Account($_POST['name'], $_POST['current_amount'], strtoupper($_POST["currency"]), $_SESSION['logged_user']);
            $accountDAO = new AccountDAO();
            if (!Validator::validateName($account->getName())) {
                throw new BadRequestException("Name must be have greater than " . MIN_LENGTH_NAME . " symbols!");
            } elseif (!Validator::validateAmount($account->getCurrentAmount())) {
                throw new BadRequestException("Amount must be between 0 and " . MAX_AMOUNT . " inclusive!");
            } elseif (!Validator::validateCurrency($account->getCurrency())){
                throw new BadRequestException(MSG_SUPPORTED_CURRENCIES);
            } else {
                $id = $accountDAO->createAccount($account);
                $account->setId($id);
                return new ResponseBody("Account created successfully!", $account);
            }
        }
        throw new BadRequestException("Bad request.");
    }

    public function getAll() {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $accountsDAO = new AccountDAO();
            $accounts = $accountsDAO->getMyAccounts($_SESSION['logged_user']);
            return new ResponseBody(null, $accounts);
        }
        throw new BadRequestException("Bad request.");
    }

    public function edit() {
        if (isset($_POST["edit"])) {
            if (!Validator::validateName($_POST["name"])) {
                throw new BadRequestException("Name must be have between " . MIN_LENGTH_NAME . " and ". " symbols!");
            }
            $account_id = $_POST["account_id"];
            $accountDAO = new AccountDAO();

            //** @Account $account */
            $account = $accountDAO->getAccountById($account_id);
            if (!$account) {
                throw new ForbiddenException("This account is not yours!");
            }
            $account->setName($_POST['name']);

            if ($account->getOwnerId() == $_SESSION['logged_user']) {
                $accountDAO->editAccount($account);
                return new ResponseBody('Account edited successfully!', $account);
            } else {
                throw new ForbiddenException("This account is not yours!");
            }
        }
        throw new BadRequestException("Bad request.");
    }

    public function delete() {
        if (isset($_POST["delete"])) {
            $account_id = $_POST["account_id"];
            $accountDAO = new AccountDAO();
            $account = $accountDAO->getAccountById($account_id);

            if ($account && $account->getOwnerId() == $_SESSION['logged_user']) {
                $accountDAO->deleteAccount($account->getId());
                return new ResponseBody("Account delete successfully!", $account);
            } else {
                throw new ForbiddenException("This account is not yours!");
            }
        }
        throw new BadRequestException("Bad request.");
    }
}