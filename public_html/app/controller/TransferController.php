<?php


namespace controller;

use exceptions\BadRequestException;
use exceptions\ForbiddenException;
use model\accounts\AccountDAO;
use model\categories\CategoryDAO;
use model\transactions\Transaction;
use model\transactions\TransactionDAO;
use model\transfers\Transfer;
use model\transfers\TransferDAO;

class TransferController {
    public function add() {
        if (isset($_POST["transfer"])) {

            if (!isset($_POST["from"]) || !isset($_POST["to"])) {
                throw new BadRequestException('The request must have from account and to account POST parameters!');
            } elseif (!isset($_POST["amount"]) || !Validator::validateAmount($_POST['amount'])) {
                throw new BadRequestException("Amount must be between 0 and " . MAX_AMOUNT . " inclusive!");
            } elseif (!isset($_POST["currency"]) || !Validator::validateCurrency(strtoupper($_POST['currency']))) {
                throw new BadRequestException(MSG_SUPPORTED_CURRENCIES);
            } elseif (!isset($_POST["time_event"]) || !Validator::validateDate($_POST['time_event'])) {
                throw new BadRequestException("Please select valid date!");
            } elseif (!isset($_POST['note']) || !Validator::validateName($_POST['note'])) {
                throw new BadRequestException("Note must be have between " . MIN_LENGTH_NAME . " and ". MAX_LENGTH_NAME . " symbols inclusive!");
            } elseif (!isset($_POST["amount"]) || !Validator::validateAmount($_POST["amount"])) {
                throw new BadRequestException("Amount must be between 0 and " . MAX_AMOUNT . " inclusive!");
            }

            $accountDAO = new AccountDAO();
            $fromAccount = $accountDAO->getAccountById($_POST["from"]);
            $toAccount = $accountDAO->getAccountById($_POST["to"]);

            if (!$fromAccount || !$toAccount) {
                throw new BadRequestException("Not such accounts.");
            } elseif ($fromAccount->getOwnerId() != $_SESSION['logged_user'] ||
                $toAccount->getOwnerId() != $_SESSION['logged_user']) {
                throw new ForbiddenException('One of the accounts is not yours.');
            } elseif ($fromAccount->getId() == $toAccount->getId()) {
                throw new BadRequestException("You can not make transfer to same account.");
            }

            $categoryDAO = new CategoryDAO();
            $category = $categoryDAO->getCategoryById(TRANSFER_CATEGORY_ID, $fromAccount->getOwnerId());
            $incomeTransaction = new Transaction($_POST["amount"], $toAccount, strtoupper($_POST['currency']), $category, $_POST['note'], $_POST['time_event']);
            $outcomeTransaction = new Transaction($_POST["amount"], $fromAccount, strtoupper($_POST['currency']), $category, $_POST['note'], $_POST['time_event']);

            $transfer = new Transfer($outcomeTransaction, $incomeTransaction);
            $transferDAO = new TransferDAO();
            $transferDAO->makeTransfer($transfer);
            return new ResponseBody("The transfer was successful!", $transfer);
        }
        throw new BadRequestException("Bad request.");
    }

    public function checkTransactionType(Transaction $transaction) {
        $transferDAO = new TransferDAO();
        $transfer = $transferDAO->getTransferByTransaction($transaction);
        if ($transfer && $transaction->getId() == $transfer->getToTransaction()->getId()) {
            return 1;
        } elseif($transfer && $transaction->getId() == $transfer->getFromTransaction()->getId()) {
            return 0;
        }
        return false;
    }
}