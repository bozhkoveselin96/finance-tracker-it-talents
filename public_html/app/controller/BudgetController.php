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
        if (isset($_POST["add_budget"])) {
            if (!isset($_POST["category_id"]) || empty($_POST['category_id'])) {
                throw new BadRequestException('Category is required!');
            } elseif (!isset($_POST["amount"]) || !Validator::validateAmount($_POST["amount"])) {
                throw new BadRequestException("Amount must be between 0 and " . MAX_AMOUNT . " inclusive!");
            } elseif (!isset($_POST["daterange"]) || empty($_POST['daterange'])) {
                throw new BadRequestException("Please select valid date range!");
            } elseif (!isset($_POST["currency"]) || !Validator::validateCurrency($_POST["currency"])) {
                throw new BadRequestException(MSG_SUPPORTED_CURRENCIES);
            }

            $daterange = explode(" - ", $_POST['daterange']);
            if (count($daterange) != 2) {
                throw new BadRequestException("Please select valid date range!");
            }
            $from_date = date_format(date_create($daterange[0]), "Y-m-d");
            $to_date = date_format(date_create($daterange[1]), "Y-m-d");
            if (!$from_date || !$to_date) {
                throw new BadRequestException("Please select valid date range!");
            }


            $categoryDAO = new CategoryDAO();
            $category = $categoryDAO->getCategoryById($_POST["category_id"], $_SESSION['logged_user']);
            if (!$category || $category->getType() != CATEGORY_OUTCOME) {
                throw new ForbiddenException('This category is not valid for Budget or is not yours!');
            }

            $budget = new Budget($category, $_POST["amount"], $_POST["currency"], $_SESSION["logged_user"], $from_date, $to_date);


            $budgetDAO = new BudgetDAO();
            $budgetDAO->createBudget($budget);
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
            if (!isset($_POST["budget_id"]) || empty($_POST['budget_id'])) {
                throw new BadRequestException('No budget to delete.');
            }
            $budgetDAO = new BudgetDAO();
            $budget = $budgetDAO->getBudgetById($_POST["budget_id"]);
            if (!$budget || $budget->getOwnerId() != $_SESSION['logged_user']) {
                throw new ForbiddenException("This budget is not yours!");
            }

            $budgetDAO->deleteBudget($budget);
            return new ResponseBody("Deleted successfully!", $budget);
        }
        throw new MethodNotAllowedException("Method not allowed!");
    }
}