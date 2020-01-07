<?php

namespace model\budgets;

use model\Connection;

class BudgetDAO {
    public function createBudget(Budget $budget) {
        $parameters = [];
        $parameters[] = $budget->getCategoryId();
        $parameters[] = $budget->getAmount();
        $parameters[] = $budget->getOwnerId();
        $parameters[] = $budget->getFromDate();
        $parameters[] = $budget->getToDate();

        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "INSERT INTO budgets(category_id, amount, owner_id, from_date, to_date, date_created)
                VALUES (?, ?, ?, ?, ?, CURRENT_DATE);";
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);
    }

    public function getAll(int $user_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "SELECT b.id, c.name, b.category_id, b.amount, b.owner_id, 
                (SELECT SUM(t.amount) FROM transactions AS t 
                WHERE t.time_event BETWEEN b.from_date AND b.to_date + INTERVAL 1 day
                AND t.category_id = b.category_id) AS progress, 
                b.from_date, b.to_date
                FROM budgets AS b
                JOIN transaction_categories AS c ON b.category_id = c.id
                WHERE b.owner_id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);

        $budgets = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $response) {
            $budget = new Budget($response->category_id, $response->amount, $response->owner_id, $response->from_date, $response->to_date);
            $budget->setProgress($response->progress);
            $budget->setCategoryName($response->name);
            $budgets[] = $budget;
        }
        return $budgets;
    }

    public function getBudgetById(int $budget_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "SELECT id, category_id, amount, from_date, to_date, owner_id 
                FROM budgets
                WHERE id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$budget_id]);
        if ($stmt->rowCount() == 1) {
            $response = $stmt->fetch(\PDO::FETCH_OBJ);
            $budget = new Budget($response->category_id, $response->amount, $response->owner_id, $response->from_date, $response->to_date);
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