<?php


namespace model\transactions;


use model\Connection;

class TransactionDAO {
    public static function create(Transaction $transaction, int $category_type) {
        try {

            $conn = Connection::get();
            $conn->beginTransaction();
                $data = [];
                $data[] = $transaction->getAmount();
                $data[] = $transaction->getAccountId();
                $data[] = $transaction->getCategoryId();
                $data[] = $transaction->getNote();
                $data[] = $transaction->getTimeEvent();

                $sql = "INSERT INTO transactions(amount, account_id, category_id, note, time_created, time_event) VALUES 
                (?, ?, ?, ?, CURRENT_TIMESTAMP, ?);";
                $stmt = $conn->prepare($sql);
                $stmt->execute($data);

                $sql2 = "UPDATE accounts SET current_amount = ROUND(current_amount + ?, 2) WHERE id = ?";
                if ($category_type == 0) {
                    $sql2 = "UPDATE accounts SET current_amount = ROUND(current_amount - ?, 2) WHERE id = ?";
                }

                $stmt2 = $conn->prepare($sql2);
                $stmt2->execute([$transaction->getAmount(), $transaction->getAccountId()]);

            $conn->commit();
            return true;
        } catch (\PDOException $exception) {
            $conn->rollBack();
            return false;
        }
    }

    public static function getByUserAndCategory(int $user_id, int $category = null) {
        try {
            $data = [];
            $data[] = $user_id;

            $conn = Connection::get();
            $sql = "SELECT t.id, t.amount, t.account_id, a.name AS account_name, t.category_id,
                    tc.name AS category_name, tc.type AS transaction_type, t.note, t.time_event
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