<?php

namespace model\budgets;

use model\accounts\AccountDAO;
use model\categories\CategoryDAO;
use model\Connection;
use model\transactions\Transaction;
use model\transactions\TransactionDAO;

class BudgetDAO {
    public function createBudget(Budget $budget) {
        $parameters = [];
        $parameters[] = $budget->getCategory()->getId();
        $parameters[] = $budget->getAmount();
        $parameters[] = $budget->getCurrency();
        $parameters[] = $budget->getOwnerId();
        $parameters[] = $budget->getFromDate();
        $parameters[] = $budget->getToDate();

        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "INSERT INTO budgets(category_id, amount, currency, owner_id, from_date, to_date, date_created)
                VALUES (?, ?, ?, ?, ?, CURRENT_DATE);";
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);
        return $conn->lastInsertId();
    }

    public function getAll(int $user_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "SELECT b.id, c.name, b.category_id, b.amount, b.currency, b.owner_id, b.from_date, b.to_date
                FROM budgets AS b
                JOIN transaction_categories AS c ON b.category_id = c.id
                WHERE b.owner_id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);

        $budgets = [];
        $categoryDAO = new CategoryDAO();
        foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $response) {
            $budget = new Budget($categoryDAO->getCategoryById($response->category_id, $_SESSION['logged_user']),
                $response->amount, $response->currency, $response->owner_id, $response->from_date, $response->to_date);
            $budget->setId($response->id);
            $budgets[] = $budget;
        }
        return $budgets;
    }

    public function getTransactionsByBudget(Budget $budget) {
        $conn = Connection::getInstance()->getConn();
        $sql = "SELECT t.id, t.amount, t.currency, t.account_id, t.category_id, t.note, t.time_event FROM transactions AS t
                JOIN accounts AS a ON a.id = t.account_id
                JOIN transaction_categories AS tc ON t.category_id = tc.id
                WHERE t.time_event BETWEEN ? AND ? + INTERVAL 1 day 
                AND a.owner_id = ? AND tc.type = 0 AND tc.id = ?";
        $parameters = [];
        $parameters[] = $budget->getFromDate();
        $parameters[] = $budget->getToDate();
        $parameters[] = $budget->getOwnerId();
        $parameters[] = $budget->getCategory()->getId();
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);

        $accountDAO = new AccountDAO();
        $categoryDAO = new CategoryDAO();
        $transactions = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $value) {
            $transaction = new Transaction($value->amount,
                $accountDAO->getAccountById($value->account_id),
                $value->currency,
                $categoryDAO->getCategoryById($value->category_id, $_SESSION['logged_user']),
                $value->note, $value->time_event);
            $transaction->setId($value->id);
            $transactions[] = $transaction;
        }
        return $transactions;
    }

    public function getBudgetById(int $budget_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "SELECT id, category_id, amount, currency, from_date, to_date, owner_id 
                FROM budgets
                WHERE id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$budget_id]);
        if ($stmt->rowCount() == 1) {
            $categoryDAO = new CategoryDAO();
            $response = $stmt->fetch(\PDO::FETCH_OBJ);
            $budget = new Budget($categoryDAO->getCategoryById($response->category_id, $_SESSION['logged_user']),
                 $response->amount, $response->currency, $response->owner_id, $response->from_date, $response->to_date);
            $budget->setId($response->id);
            return $budget;
        }
        return false;
    }

    public function deleteBudget(int $budget_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "DELETE FROM budgets WHERE id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$budget_id]);
    }
}