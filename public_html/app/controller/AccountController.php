<?php


namespace controller;


use exceptions\BadRequestException;
use exceptions\ForbiddenException;
use exceptions\MethodNotAllowedException;
use Interfaces\Deletable;
use Interfaces\Editable;
use model\accounts\Account;
use model\accounts\AccountDAO;

class AccountController implements Editable, Deletable {
    public function add() {
        if (isset($_POST['add_account'])) {
            if (!Validator::validateName($_POST["name"])) {
                throw new BadRequestException("Name must be have greater than " . MIN_LENGTH_NAME . " symbols!");
            } elseif (!Validator::validateAmount($_POST["current_amount"])) {
                throw new BadRequestException("Amount must be between 0 and " . MAX_AMOUNT . " inclusive!");
            } elseif (!Validator::validateCurrency($_POST["currency"])){
                throw new BadRequestException(MSG_SUPPORTED_CURRENCIES);
            }
            $account = new Account($_POST['name'], $_POST['current_amount'], strtoupper($_POST["currency"]), $_SESSION['logged_user']);
            $accountDAO = new AccountDAO();
            $accountDAO->createAccount($account);
            return new ResponseBody("Account created successfully!", $account);
        }
        throw new MethodNotAllowedException("Method not allowed.");
    }

    public function getAll() {
        if ($_SERVER['REQUEST_METHOD'] == 'GET') {
            $accountsDAO = new AccountDAO();
            $accounts = $accountsDAO->getMyAccounts($_SESSION['logged_user']);
            return new ResponseBody(null, $accounts);
        }
        throw new MethodNotAllowedException("Method not allowed!");
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

            if ($account->getOwnerId() != $_SESSION['logged_user']) {
                throw new ForbiddenException("This account is not yours!");
            }

            $accountDAO->editAccount($account);
            return new ResponseBody('Account edited successfully!', $account);
        }
        throw new MethodNotAllowedException("Method not allowed!");
    }

    public function delete() {
        if (isset($_POST["delete"])) {
            $account_id = $_POST["account_id"];
            $accountDAO = new AccountDAO();
            $account = $accountDAO->getAccountById($account_id);

            if (!$account || $account->getOwnerId() != $_SESSION['logged_user']) {
                throw new ForbiddenException("This account is not yours!");
            }
            $accountDAO->deleteAccount($account->getId());
            return new ResponseBody("Account delete successfully!", $account);
        }
        throw new MethodNotAllowedException("Method not allowed!");
    }
}