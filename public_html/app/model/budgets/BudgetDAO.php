<?php

namespace model\budgets;

use model\Connection;

class BudgetDAO {
    public static function createBudget(Budget $budget) {
        try {
            $data = [];
            $data[] = $budget->getCategoryId();
            $data[] = $budget->getAmount();
            $data[] = $budget->getOwnerId();
            $data[] = $budget->getFromDate();
            $data[] = $budget->getToDate();

            $conn = Connection::get();
            $sql = "INSERT INTO budgets(category_id, amount, owner_id, from_date, to_date, date_created)
                    VALUES (?, ?, ?, ?, ?, CURRENT_DATE);";
            $stmt = $conn->prepare($sql);
            $stmt->execute($data);
            return true;

        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function getAll(int $user_id) {
        try {
            $conn = Connection::get();
            $sql = "SELECT b.id, c.name, b.amount, 
                    (SELECT SUM(t.amount) FROM transactions AS t 
                    WHERE t.time_event BETWEEN b.from_date AND b.to_date + INTERVAL 1 day
                    AND t.category_id = b.category_id) AS budget_status, 
                    b.from_date, b.to_date
                    FROM budgets AS b
                    JOIN transaction_categories AS c ON b.category_id = c.id
                    WHERE b.owner_id = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute(["$user_id"]);
            if ($stmt->rowCount() > 0) {
                return $stmt->fetchAll(\PDO::FETCH_OBJ);
            }
            return false;
        } catch (\PDOException $exception) {
            return $exception;
        }
    }

    public static function getBudgetById(int $budget_id) {
        try {
            $conn = Connection::get();
            $sql = "SELECT id, category_id, amount, from_date, to_date, owner_id 
                    FROM budgets
                    WHERE id = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$budget_id]);
            if ($stmt->rowCount() == 1) {
                return $stmt->fetch(\PDO::FETCH_OBJ);
            }
            return false;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function deleteBudget(int $budget_id) {
        try {
            $conn = Connection::get();
            $sql = "DELETE FROM budgets WHERE id = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$budget_id]);
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }
}