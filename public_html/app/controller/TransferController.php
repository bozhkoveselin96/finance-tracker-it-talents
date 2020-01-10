<?php


namespace controller;

use exceptions\BadRequestException;
use model\accounts\AccountDAO;
use model\categories\CategoryDAO;
use model\transactions\Transaction;
use model\transactions\TransactionDAO;
use model\transfers\Transfer;
use model\transfers\TransferDAO;

class TransferController {
    public function add() {
        if (isset($_POST["transfer"]) && isset($_POST["from"]) && isset($_POST["to"]) &&
            isset($_POST["amount"]) && $_POST["currency"] && isset($_POST["time_event"]) && $_POST['note']) {
            $accountDAO = new AccountDAO();
            $fromAccount = $accountDAO->getAccountById($_POST["from"]);
            $toAccount = $accountDAO->getAccountById($_POST["to"]);

            if (!$fromAccount || !$toAccount) {
                throw new BadRequestException("No such account.");
            }

            $categoryDAO = new CategoryDAO();
            $category = $categoryDAO->getCategoryById(TRANSFER_CATEGORY_ID, $fromAccount->getOwnerId());
            $incomeTransaction = new Transaction($_POST["amount"], $fromAccount, strtoupper($_POST['currency']), $category, $_POST['note'], $_POST['time_event']);
            $outcomeTransaction = new Transaction($_POST["amount"], $toAccount, strtoupper($_POST['currency']), $category, $_POST['note'], $_POST['time_event']);

            $transfer = new Transfer($_POST["amount"], strtoupper($_POST["currency"]), $fromAccount, $toAccount,$_POST["time_event"]);
            if (!Validator::validateAmount($transfer->getAmount())) {
                throw new BadRequestException("Amount must be between 0 and " . MAX_AMOUNT . " inclusive!");
            } elseif (!Validator::validateCurrency($transfer->getCurrency())) {
                throw new BadRequestException(MSG_SUPPORTED_CURRENCIES);
            } elseif (!Validator::validateDate($transfer->getTimeEvent())) {
                throw new BadRequestException("Please select valid day!");
            } elseif (!Validator::validateName($incomeTransaction->getNote())) {
                throw new BadRequestException("Note must be have between " . MIN_LENGTH_NAME . " and ". MAX_LENGTH_NAME . " symbols inclusive!");
            }

            $transferDAO = new TransferDAO();
            $identifiers = $transferDAO->makeTransfer($transfer, $incomeTransaction, $outcomeTransaction);
            $transfer->setId($identifiers["transfer_id"]);
            $incomeTransaction->setId( $identifiers["transaction_income_id"]);
            $outcomeTransaction->setId($identifiers["transaction_outcome_id"]);

            return new ResponseBody("The transfer was successful!", $transfer);
        }
        throw new BadRequestException("Bad request.");
    }
}