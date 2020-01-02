<?php


namespace model;


class StatisticDAO {
    public static function getTransactionsSum($user_id, $type) {
        try {
            $conn = Connection::get();
            $name = ($type == 1 ? "income" : "outcome");

            $sql = "SELECT COALESCE(SUM(t.amount), 0) AS ? FROM transactions AS t
                    JOIN accounts AS a ON(a.id = t.account_id)
                    JOIN transaction_categories AS tc ON(t.category_id = tc.id)
                    WHERE a.owner_id = ? AND tc.type = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name, $user_id, $type]);
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function getTransactionsByCategory($user_id, $type) {
        try {
            $conn = Connection::get();
            $sql = "SELECT tc.name AS category_name, COALESCE(SUM(t.amount), 0) AS amount 
                    FROM transactions AS t
                    JOIN accounts AS a ON(a.id = t.account_id)
                    JOIN transaction_categories AS tc ON(t.category_id = tc.id)
                    WHERE a.owner_id = ? AND tc.type = ?    
                    GROUP BY t.category_id;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id, $type]);
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function getTrForSelectedPeriod($user_id, $type, $from_date, $to_date) {
        try {
            $conn = Connection::get();
            $sql = "SELECT COALESCE(SUM(t.amount), 0) AS money FROM transactions AS t
                    JOIN accounts AS a ON(a.id = t.account_id)
                    JOIN transaction_categories AS tc ON(t.category_id = tc.id)
                    WHERE a.owner_id = ? AND tc.type = ? AND t.time_event BETWEEN ? AND ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id, $type, $from_date, $to_date]);
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function getTrForSelectedPeriodByCategories($user_id, $type, $from_date, $to_date) {
        try {
            $conn = Connection::get();
            $sql = "SELECT tc.name AS category_name,
                    COALESCE(SUM(t.amount), 0) AS money FROM transactions AS t
                    JOIN accounts AS a ON(a.id = t.account_id)
                    JOIN transaction_categories AS tc ON(t.category_id = tc.id)
                    WHERE a.owner_id = ? AND tc.type = ? AND t.time_event BETWEEN ? AND ?
                    GROUP BY t.category_id;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id, $type, $from_date, $to_date]);
            return $stmt->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function getBudgetProgress($budget_id, $owner_id) {
        try {
            $conn = Connection::get();
            $sql = "SELECT (b.amount-SUM(t.amount)) AS remainder, SUM(t.amount) AS spent_so_far
                    FROM budgets AS b
                    JOIN transactions AS t ON(b.category_id = t.category_id)
                    WHERE t.time_event BETWEEN b.from_date AND b.to_date 
                    AND b.id = ? AND b.user_id = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$budget_id, $owner_id]);
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\PDOException $exception) {
            return false;
        }
    }
}