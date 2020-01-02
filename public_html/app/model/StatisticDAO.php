<?php


namespace model;


class StatisticDAO {

    public static function getTransactionsSum($user_id, $type) {
        try {
            $conn = Connection::get();
            if ($type == 1) {
                $name = 'Income';
            } else {
                $name = 'Outcome';
            }
            $sql = "SELECT COALESCE( SUM(t.amount), 0) AS ? FROM transactions AS t
                    JOIN accounts AS a ON(a.id = t.account_id)
                    JOIN transaction_categories AS tc ON(t.category_id = tc.id)
                    WHERE a.owner_id = ? AND tc.type = ?;";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$name,$user_id, $type]);
            return $stmt->fetch(\PDO::FETCH_OBJ);
        } catch (\PDOException $exception) {
            return false;
        }
    }
}