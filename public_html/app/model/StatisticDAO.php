<?php


namespace model;


class StatisticDAO {
    public static function getTransactionsSum($user_id, $type) {
        try {
            $conn = Connection::get();
            $sql = "SELECT COALESCE( SUM(t.amount), 0) AS sum FROM transactions AS t
                    JOIN accounts AS a ON(a.id = t.account_id)
                    JOIN transaction_categories AS tc ON(t.category_id = tc.id)
                    WHERE a.owner_id = ? AND tc.type = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id, $type]);
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\PDOException $exception) {
            return false;
        }
    }
}