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
}