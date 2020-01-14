<?php


namespace controller;


use exceptions\BadRequestException;
use exceptions\ForbiddenException;
use interfaces\Deletable;
use exceptions\MethodNotAllowedException;
use model\budgets\Budget;
use model\budgets\BudgetDAO;
use model\categories\CategoryDAO;
use model\CurrencyDAO;
use model\transactions\Transaction;

class BudgetController implements Deletable {
    public function add() {
        if (isset($_POST["add_budget"]) && isset($_POST["category_id"]) && isset($_POST["amount"]) &&
            isset($_POST["daterange"]) && !empty($_POST['daterange']) && isset($_POST["currency"])) {

            $daterange = explode(" - ", $_POST['daterange']);
            if (count($daterange) != 2) {
                throw new BadRequestException("Please select valid date range!");
            }
            $from_date = date_format(date_create($daterange[0]), "Y-m-d");
            $to_date = date_format(date_create($daterange[1]), "Y-m-d");
            if (!Validator::validateDate($from_date) || !Validator::validateDate($to_date)) {
                throw new BadRequestException("Please select valid date range!");
            }

            if (!Validator::validateAmount($_POST["amount"])) {
                throw new BadRequestException("Amount must be between 0 and " . MAX_AMOUNT . " inclusive!");
            } elseif (!Validator::validateCurrency($_POST["currency"])) {
                throw new BadRequestException(MSG_SUPPORTED_CURRENCIES);
            }
            $categoryDAO = new CategoryDAO();
            $category = $categoryDAO->getCategoryById($_POST["category_id"], $_SESSION['logged_user']);
            $budget = new Budget($category, $_POST["amount"], $_POST["currency"], $_SESSION["logged_user"], $from_date, $to_date);


            $budgetDAO = new BudgetDAO();
            $id = $budgetDAO->createBudget($budget);
            $budget->setId($id);
            $sum = 0;
            $currencyDAO = new CurrencyDAO();
            $transactionsByBudget = $budgetDAO->getTransactionsByBudget($budget);
            /** @var Transaction $transaction */
            foreach ($transactionsByBudget as $transaction) {
                if ($budget->getCurrency() == $transaction->getCurrency()) {
                    $sum += $transaction->getAmount();
                } else {
                    $sum += $currencyDAO->currencyConverter($transaction->getAmount(), $transaction->getCurrency(), $budget->getCurrency());
                }
            }
            $budget->setProgress($sum);
            return new ResponseBody('Budget added successfully!', $budget);
        }
        throw new MethodNotAllowedException("Method not allowed!");
    }

    public function getAll() {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $budgetDAO = new BudgetDAO();
            $budgets = $budgetDAO->getAll($_SESSION['logged_user']);

            $currencyDAO = new CurrencyDAO();
            /** @var Budget $budget */
            foreach ($budgets as $budget) {
                $sum = 0;
                $transactionsByBudget = $budgetDAO->getTransactionsByBudget($budget);
                /** @var Transaction $transaction */
                foreach ($transactionsByBudget as $transaction) {
                    if ($budget->getCurrency() == $transaction->getCurrency()) {
                        $sum += $transaction->getAmount();
                    } else {
                        $sum += $currencyDAO->currencyConverter($transaction->getAmount(), $transaction->getCurrency(), $budget->getCurrency());
                    }
                }
                $budget->setProgress(round($sum, 2));
            }
            return new ResponseBody(null, $budgets);
        }
        throw new MethodNotAllowedException("Method not allowed!");
    }

    public function delete() {
        if (isset($_POST["delete"])) {
            $budgetDAO = new BudgetDAO();
            $budget = $budgetDAO->getBudgetById($_POST["budget_id"]);
            if (!$budget || $budget->getOwnerId() != $_SESSION['logged_user']) {
                throw new ForbiddenException("This budget is not yours!");
            }

            $budgetDAO->deleteBudget($budget->getId());
            return new ResponseBody("Deleted successfully!", $budget);
        }
        throw new MethodNotAllowedException("Method not allowed!");
    }
}