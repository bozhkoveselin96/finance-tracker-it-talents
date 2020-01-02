<?php


namespace controller;


use model\budgets\Budget;
use model\budgets\BudgetDAO;
use model\categories\CategoryDAO;

class BudgetController {
    public function add() {
        $response = [];
        echo Validator::validateDate($_POST['from_date']);
        $status = STATUS_BAD_REQUEST . 'Something is not filled correctly or you are not logged in.';
        if (isset($_POST["add_budget"]) && isset($_SESSION["logged_user"]) &&
            isset($_POST["category_id"]) && isset($_POST["amount"]) &&
            isset($_POST["from_date"]) && isset($_POST["to_date"])) {
                $category = CategoryDAO::getCategoryById($_POST["category_id"], $_SESSION['logged_user']);
                $amount = $_POST["amount"];
                $owner_id = $_SESSION["logged_user"];
                $from_date = $_POST["from_date"];
                $to_date = $_POST["to_date"];

                $budget = new Budget($category->id, $amount, $owner_id, $from_date, $to_date);
                if ($category && Validator::validateAmount($budget->getAmount()) && Validator::validateDate($from_date) &&
                    Validator::validateDate($to_date) && BudgetDAO::createBudget($budget)) {
                    $status = STATUS_CREATED;
                    $response["target"] = "budget";
                }
        }
        header($status);
        return $response;
    }

    public function getAll() {
        $response = [];
        $status = STATUS_BAD_REQUEST . 'No budgets available or you are not logged in.';
        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_SESSION["logged_user"])) {
            $budgets = BudgetDAO::getAll($_SESSION['logged_user']);
            if ($budgets) {
                $response["data"] = $budgets;
                $status = STATUS_OK;
            }

        }
        header($status);
        return $response;
    }

    public function delete() {
        $response = [];
        $status = STATUS_FORBIDDEN . 'You do not have access to this!';
        if (isset($_POST["delete"]) && isset($_SESSION['logged_user'])) {
            $budget = BudgetDAO::getBudgetById($_POST["budget_id"]);

            if ($budget && $_SESSION['logged_user'] == $budget->owner_id && BudgetDAO::deleteBudget($budget->id)) {
                $status = STATUS_OK;
            }
        }
        header($status);
        return $response;
    }
}