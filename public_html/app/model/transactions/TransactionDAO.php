<?php


namespace model\transactions;


use model\Connection;
use mysql_xdevapi\Exception;

class TransactionDAO {
    public function create(Transaction $transaction, int $category_type) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        try {
            $conn->beginTransaction();
                $parameters = [];
                $parameters[] = $transaction->getAmount();
                $parameters[] = $transaction->getAccountId();
                $parameters[] = $transaction->getCategoryId();
                $parameters[] = $transaction->getNote();
                $parameters[] = $transaction->getTimeEvent();

                $sql = "INSERT INTO transactions(amount, account_id, category_id, note, time_created, time_event) 
                        VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, ?);";
                $stmt = $conn->prepare($sql);
                $stmt->execute($parameters);

                $sql2 = "UPDATE accounts SET current_amount = ROUND(current_amount + ?, 2) WHERE id = ?";
                if ($category_type == 0) {
                    $sql2 = "UPDATE accounts SET current_amount = ROUND(current_amount - ?, 2) WHERE id = ?";
                }

                $stmt2 = $conn->prepare($sql2);
                $stmt2->execute([$transaction->getAmount(), $transaction->getAccountId()]);

            $conn->commit();
        } catch (\PDOException $exception) {
            $conn->rollBack();
            throw new Exception($exception->getMessage());
        }
    }

    public function getByUserAndCategory(int $user_id, int $category = null) {
        $data = [];
        $data[] = $user_id;

        $instance = Connection::getInstance();
        $conn = $instance->getConn();
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
        $stmt->execute($data);
        $transactions = [];

        foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $value) {
            $transaction = new Transaction($value->amount, $value->account_id, $value->category_id, $value->note, $value->time_event);
            $transaction->setId($value->id);
            $transaction->setAccountName($value->account_name);
            $transaction->setCategoryName($value->category_name);
            $transaction->setCategoryType($value->transaction_type);
            $transactions[] = $transaction;
        }
        return $transactions;
    }

    public function getTransactionById(int $transaction_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "SELECT id, amount, account_id, category_id, note, time_event FROM accounts WHERE id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$transaction_id]);
        if ($stmt->rowCount() == 1) {
            $response = $stmt->fetch(\PDO::FETCH_OBJ);
            $transaction = new Transaction($response->amount, $response->account_id, $response->category_id, $response->note, $response->time_event);
            $transaction->setId($response->id);
            return $transaction;
        }
        return false;
    }

    public function deleteTransaction($transaction_id, $account_id, $transaction_amount, $account_amount) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        try {
            $conn->beginTransaction();

            $sql1 = "DELETE FROM transaction WHERE id = ?;";
            $stmt = $conn->prepare($sql1);
            $stmt->execute([$transaction_id]);

            $account_amount += $transaction_amount ;
            $sql2 = "UPDATE accounts SET current_amount = ? WHERE id = ?;";
            $stmt = $conn->prepare($sql2);
            $stmt->execute([$account_amount, $account_id]);

            $conn->commit();
        } catch (\PDOException $exception) {
            $conn->rollBack();
            throw new Exception($exception->getMessage());
        }
    }
}