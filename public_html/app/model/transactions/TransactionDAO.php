<?php


namespace model\transactions;


use model\Connection;

class TransactionDAO {
    public static function create(Transaction $transaction) {
        try {
            $data = [];
            $data[] = $transaction->getAmount();
            $data[] = $transaction->getAccountId();
            $data[] = $transaction->getCategoryId();
            $data[] = $transaction->getNote();
            $data[] = $transaction->getTimeEvent();

            $conn = Connection::get();
            $sql = "INSERT INTO transactions(amount, account_id, category_id, note, time_created, time_event) VALUES 
            (?, ?, ?, ?, CURRENT_TIMESTAMP, ?);";
            $stmt = $conn->prepare($sql);
            if ($stmt->execute($data)) {
                return true;
            }
        } catch (\PDOException $exception) {
            return $exception;
        }
    }

    public static function getByUserAndCategory(int $user_id, int $category = null) {
        try {
            $data = [];
            $data[] = $user_id;

            $conn = Connection::get();
            $sql = "SELECT t.id, t.amount, t.account_id, a.name AS account_name, t.category_id,
                    tc.name AS category_name, t.note, t.time_event
                    FROM transactions AS t
                    JOIN accounts AS a ON t.account_id = a.id
                    JOIN transaction_categories AS tc ON t.category_id = tc.id
                    WHERE a.owner_id = ? ";
            if ($category != null) {
                $data[] = $category;
                $sql .= "AND tc.id = ? ";
            }
            $sql .= "ORDER BY time_created DESC";

            $stmt = $conn->prepare($sql);
            if ($stmt->execute($data)) {
                return $stmt->fetchAll(\PDO::FETCH_OBJ);
            }
        } catch (\PDOException $exception) {
            return false;
        }
    }
}