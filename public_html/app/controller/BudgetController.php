<?php


namespace controller;


use exceptions\BadRequestException;
use exceptions\ForbiddenException;
use model\budgets\Budget;
use model\budgets\BudgetDAO;
use model\categories\CategoryDAO;

class BudgetController {
    public function add() {
        $response = [];
        if (isset($_POST["add_budget"]) && isset($_POST["category_id"]) && isset($_POST["amount"]) &&
            isset($_POST["from_date"]) && isset($_POST["to_date"])) {
            $categoryDAO = new CategoryDAO();
            $category = $categoryDAO->getCategoryById($_POST["category_id"], $_SESSION['logged_user']);
            $budget = new Budget($category, $_POST["amount"], $_SESSION["logged_user"], $_POST["from_date"], $_POST["to_date"]);

            if (!Validator::validateAmount($budget->getAmount())) {
                throw new BadRequestException("Amount must be between 0 and" . MAX_AMOUNT . "inclusive");
            } elseif (!Validator::validateDate($budget->getFromDate()) && !Validator::validateDate($budget->getToDate())) {
                throw new BadRequestException("Please select valid date");
            }
            $budgetDAO = new BudgetDAO();
            $budgetDAO->createBudget($budget);
            $response["target"] = "budget";

        }
        return $response;
    }

    public function getAll() {
        $response = [];
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $budgetDAO = new BudgetDAO();
            $budgets = $budgetDAO->getAll($_SESSION['logged_user']);
            $response["data"] = $budgets;
        }
        return $response;
    }

    public function delete() {
        $response = [];
        if (isset($_POST["delete"])) {
            $budgetDAO = new BudgetDAO();
            $budget = $budgetDAO->getBudgetById($_POST["budget_id"]);
            if ($budget->getOwnerId() == $_SESSION['logged_user']) {
                $budgetDAO->deleteBudget($budget->getId());
            } else {
                throw new ForbiddenException("This budget is not yours");
            }
        }
        return $response;
    }
}