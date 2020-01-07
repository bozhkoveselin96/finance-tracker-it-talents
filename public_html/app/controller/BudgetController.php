<?php


namespace controller;


use model\budgets\Budget;
use model\budgets\BudgetDAO;
use model\categories\CategoryDAO;

class BudgetController {
    public function add() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'Something is not filled correctly.';
        if (isset($_POST["add_budget"]) && isset($_POST["category_id"]) && isset($_POST["amount"]) &&
            isset($_POST["from_date"]) && isset($_POST["to_date"])) {
            try {
                $categoryDAO = new CategoryDAO();
                $category = $categoryDAO->getCategoryById($_POST["category_id"], $_SESSION['logged_user']);

                $budget = new Budget($category->getId(), $_POST["amount"], $_SESSION["logged_user"], $_POST["from_date"], $_POST["to_date"]);
                if (Validator::validateAmount($budget->getAmount()) && Validator::validateDate($budget->getFromDate()) &&
                    Validator::validateDate($budget->getToDate())) {
                    $budgetDAO = new BudgetDAO();
                    $budgetDAO->createBudget($budget);
                    $status = STATUS_CREATED;
                    $response["target"] = "budget";
                }
            } catch (\Exception $exception) {
                $status = STATUS_ACCEPTED . 'Not created. Please try again';
            }
        }
        header($status);
        return $response;
    }

    public function getAll() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'No budgets available.';
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            try {
                $budgetDAO = new BudgetDAO();
                $budgets = $budgetDAO->getAll($_SESSION['logged_user']);
                $response["data"] = $budgets;
                $status = STATUS_OK;
            } catch (\Exception $exception) {
                $status = STATUS_ACCEPTED . 'Not available. Please try again';
            }
        }
        header($status);
        return $response;
    }

    public function delete() {
        $response = [];
        $status = STATUS_FORBIDDEN . 'You do not have access to this!';
        if (isset($_POST["delete"])) {
            try {
                $budgetDAO = new BudgetDAO();
                $budget = $budgetDAO->getBudgetById($_POST["budget_id"]);
                if ($budget->getOwnerId() == $_SESSION['logged_user']) {
                    $budgetDAO->deleteBudget($budget->getId());
                    $status = STATUS_OK;
                }
            } catch (\Exception $exception) {
                $status = STATUS_ACCEPTED . 'Not deleted. Please try again';
            }
        }
        header($status);
        return $response;
    }
}