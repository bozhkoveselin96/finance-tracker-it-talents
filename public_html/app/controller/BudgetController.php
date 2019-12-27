<?php


namespace controller;


use model\budgets\Budget;
use model\budgets\BudgetDAO;

class BudgetController {
    public function add() {
        $response = [];
        $response["status"] = false;
        if (isset($_POST["add_budget"]) && isset($_SESSION["logged_user"]) &&
            isset($_POST["category_id"]) && isset($_POST["amount"]) &&
            isset($_POST["from_date"]) && isset($_POST["to_date"])) {
                $category_id = $_POST["category_id"];
                $amount = $_POST["amount"];
                $owner_id = $_SESSION["logged_user"];
                $from_date = $_POST["from_date"];
                $to_date = $_POST["to_date"];

                $budget = new Budget($category_id, $amount, $owner_id, $from_date, $to_date);
                if (BudgetDAO::createBudget($budget)) {
                    $response["status"] = true;
                    $response["target"] = "budget";
                }
        }
        return $response;
    }

    public function getAll() {
        $response = [];
        $response["status"] = false;

        if ($_SERVER["REQUEST_METHOD"] == "GET" && isset($_SESSION["logged_user"]) && isset($_GET["user_id"])) {
            $user_id = $_GET["user_id"];
            if (Validator::validateLoggedUser($user_id)) {
                $budgets = BudgetDAO::getAll($user_id);
                if ($budgets) {
                    $response["status"] = true;
                    $response["data"] = $budgets;
                }
            }
        }
        return $response;
    }

    public function delete() {
        $response = [];
        $response["status"] = false;
        if (isset($_POST["delete"])) {
            $user_id = $_POST["user_id"];
            $budget_id = $_POST["budget_id"];
            $budget = BudgetDAO::getBudgetById($budget_id);

            if ($budget && $user_id && Validator::validateLoggedUser($user_id) &&
                $user_id == $budget->owner_id && BudgetDAO::deleteBudget($budget_id)) {
                    $response["status"] = true;
            }
        }
        return $response;
    }
}