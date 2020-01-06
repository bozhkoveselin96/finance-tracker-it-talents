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
            $conn = Connection::get();
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

    public static function getForTheLastThirtyDays($owner_id) {
        try {
            $conn = Connection::get();
            $sql1 = "
select DATE_FORMAT(NOW(), '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS incomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =1 AND DATE(t.time_event) = DATE(NOW()) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 1 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS incomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =1 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 1 DAY) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 2 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS incomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =1 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 2 DAY) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 3 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS incomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =1 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 3 DAY) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 4 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS incomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =1 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 4 DAY) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 5 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS incomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =1 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 5 DAY) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 6 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS incomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =1 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 6 DAY) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 7 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS incomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =1 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 7 DAY) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 8 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS incomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =1 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 8 DAY) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 9 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS incomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =1 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 9 DAY) AND a.owner_id = 14";
            $incomes = $conn->prepare($sql1);
            $incomes->execute([$owner_id]);

            $sql2 = "
            select DATE_FORMAT(NOW(), '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS outcomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =0 AND DATE(t.time_event) = DATE(NOW()) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 1 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS outcomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =0 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 1 DAY) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 2 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS outcomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =0 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 2 DAY) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 3 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS outcomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =0 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 3 DAY) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 4 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS outcomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =0 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 4 DAY) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 5 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS outcomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =0 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 5 DAY) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 6 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS outcomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =0 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 6 DAY) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 7 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS outcomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =0 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 7 DAY) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 8 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS outcomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =0 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 8 DAY) AND a.owner_id = 14  UNION
select DATE_FORMAT(NOW() - INTERVAL 9 DAY, '%e.%m') as day, COALESCE(SUM(t.amount), 0) AS outcomes FROM transactions t JOIN transaction_categories tc ON tc.id = t.category_id  JOIN accounts a ON a.id = t.account_id WHERE tc.type =0 AND DATE(t.time_event) = DATE(NOW() - INTERVAL 9 DAY) AND a.owner_id = 14 ";
            $costs = $conn->prepare($sql2);
            $costs->execute([$owner_id]);

            $stmt = [];
            $stmt[] = array_reverse($incomes->fetchAll(\PDO::FETCH_OBJ));
            $stmt[] = array_reverse($costs->fetchAll(\PDO::FETCH_OBJ));
            return $stmt;
        } catch (\PDOException $exception) {
            return false;
        }
    }
}