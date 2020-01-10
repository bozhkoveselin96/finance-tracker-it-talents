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
            isset($_POST["amount"]) && $_POST["currency"] && isset($_POST["time_event"]) &&
            isset($_POST["category_transfer"])) {
            $accountDAO = new AccountDAO();
            $fromAccount = $accountDAO->getAccountById($_POST["from"]);
            $toAccount = $accountDAO->getAccountById($_POST["to"]);

            $categoryDAO = new CategoryDAO();
            $category = $categoryDAO->getCategoryById($_POST["category_transfer"], $fromAccount->getOwnerId());

            if (!$fromAccount || !$toAccount) {
                throw new BadRequestException("No such account.");
            }

            $incomeTransaction = new Transaction($_POST["amount"], $fromAccount, strtoupper($_POST['currency']), $category, "Transfer from " . $fromAccount->getName(), $_POST['time_event']);
            $outcomeTransaction = new Transaction($_POST["amount"], $toAccount, strtoupper($_POST['currency']), $category, "Transfer to " . $toAccount->getName(), $_POST['time_event']);

            $transfer = new Transfer($_POST["amount"], $fromAccount, $toAccount, strtoupper($_POST["currency"]),$_POST["time_event"]);
            if (!Validator::validateAmount($transfer->getAmount())) {
                throw new BadRequestException("Amount must be between 0 and " . MAX_AMOUNT . " inclusive!");
            } elseif (!Validator::validateCurrency($transfer->getCurrency())) {
                throw new BadRequestException(MSG_SUPPORTED_CURRENCIES);
            } elseif (!Validator::validateDate($transfer->getTimeEvent())) {
                throw new BadRequestException("Please select valid day!");
            }

            $transferDAO = new TransferDAO();
            $identifiers = $transferDAO->makeTransfer($transfer, $incomeTransaction, $outcomeTransaction);
            $transfer_id = $identifiers["transfer_id"];
            $transfer->setId($transfer_id);

            $income_id = $identifiers["transaction_income_id"];
            $incomeTransaction->setId($income_id);
            $outcome_id = $identifiers["transaction_outcome_id"];
            $outcomeTransaction->setId($outcome_id);

            return new ResponseBody("The transfer was successful!", $transfer);
        }
        throw new BadRequestException("Bad request.");
    }
}