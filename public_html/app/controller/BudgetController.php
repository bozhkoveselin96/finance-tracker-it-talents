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
                throw new BadRequestException("Amount must be between 0 and " . MAX_AMOUNT . " inclusive!");
            } elseif (!Validator::validateDate($budget->getFromDate()) && !Validator::validateDate($budget->getToDate())) {
                throw new BadRequestException("Please select valid date");
            }
            $budgetDAO = new BudgetDAO();
            $id = $budgetDAO->createBudget($budget);
            $budget->setId($id);
            return new ResponseBody('Budget added successfully!', $budget);
        }
        throw new BadRequestException("Bad request.");
    }

    public function getAll() {
        if ($_SERVER["REQUEST_METHOD"] == "GET") {
            $budgetDAO = new BudgetDAO();
            $budgets = $budgetDAO->getAll($_SESSION['logged_user']);
            return new ResponseBody(null, $budgets);
        }
        throw new BadRequestException("Bad request.");
    }

    public function delete() {
        if (isset($_POST["delete"])) {
            $budgetDAO = new BudgetDAO();
            $budget = $budgetDAO->getBudgetById($_POST["budget_id"]);
            if ($budget && $budget->getOwnerId() == $_SESSION['logged_user']) {
                $budgetDAO->deleteBudget($budget->getId());
                return new ResponseBody("Delete successfully!", $budget);
            } else {
                throw new ForbiddenException("This budget is not yours");
            }
        }
        throw new BadRequestException("Bad request.");
    }
}