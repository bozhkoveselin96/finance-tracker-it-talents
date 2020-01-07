<?php


namespace model;


class StatisticDAO {

    public static function getTransactionsSum($user_id, $type) {
        try {
            $instance = Connection::getInstance();
            $conn = $instance->getConn();
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
            $instance = Connection::getInstance();
            $conn = $instance->getConn();
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
            $instance = Connection::getInstance();
            $conn = $instance->getConn();
            $name = ($type == 1 ? "income" : "outcome");
            $sql = "SELECT COALESCE(SUM(t.amount), 0) AS ? FROM transactions AS t
                    JOIN accounts AS a ON(a.id = t.account_id)
                    JOIN transaction_categories AS tc ON(t.category_id = tc.id)
                    WHERE a.owner_id = ? AND tc.type = ? AND t.time_event BETWEEN ? AND ? + INTERVAL 1 DAY;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name,$user_id, $type, $from_date, $to_date]);
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function getTrForSelectedPeriodByCategories($user_id, $type, $from_date, $to_date) {
        try {
            $instance = Connection::getInstance();
            $conn = $instance->getConn();
            $sql = "SELECT tc.name AS category_name,
                    COALESCE(SUM(t.amount), 0) AS amount FROM transactions AS t
                    JOIN accounts AS a ON(a.id = t.account_id)
                    JOIN transaction_categories AS tc ON(t.category_id = tc.id)
                    WHERE a.owner_id = ? AND tc.type = ? AND t.time_event BETWEEN ? AND ? + INTERVAL 1 DAY
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
            $instance = Connection::getInstance();
            $conn = $instance->getConn();
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

    public static function getForTheLastTenDays($owner_id) {
        try {
            $instance = Connection::getInstance();
            $conn = $instance->getConn();
            $sql1 = "SELECT DATE_FORMAT(t.time_event, '%e.%m') AS date, tc.type AS category, ROUND(SUM(t.amount), 2) as sum FROM transactions AS t
                    JOIN transaction_categories AS tc ON tc.id = t.category_id
                    JOIN accounts AS a ON a.id = t.account_id
                    WHERE a.owner_id = 14 AND t.time_event > NOW() - INTERVAL 10 day AND t.time_event < NOW()
                    GROUP BY date, tc.type
                    ORDER BY date";
            $transactions = $conn->prepare($sql1);
            $transactions->execute([$owner_id]);
            return $transactions->fetchAll(\PDO::FETCH_OBJ);
        } catch (\PDOException $exception) {
            return false;
        }
    }
}