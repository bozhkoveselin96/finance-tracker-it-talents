<?php


namespace model\transactions;


use model\accounts\AccountDAO;
use model\categories\CategoryDAO;
use model\Connection;
use mysql_xdevapi\Exception;

class TransactionDAO {
    public function create(Transaction $transaction) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        try {
            $conn->beginTransaction();
                $parameters = [];
                $parameters[] = $transaction->getAmount();
                $parameters[] = $transaction->getAccount()->getId();
                $parameters[] = $transaction->getCategory()->getId();
                $parameters[] = $transaction->getNote();
                $parameters[] = $transaction->getTimeEvent();

                $sql = "INSERT INTO transactions(amount, account_id, category_id, note, time_created, time_event) 
                        VALUES (?, ?, ?, ?, CURRENT_TIMESTAMP, ?);";
                $stmt = $conn->prepare($sql);
                $stmt->execute($parameters);

                $sql2 = "UPDATE accounts SET current_amount = ROUND(current_amount + ?, 2) WHERE id = ?";
                if ($transaction->getCategory()->getType() == 0) {
                    $sql2 = "UPDATE accounts SET current_amount = ROUND(current_amount - ?, 2) WHERE id = ?";
                }

                $stmt2 = $conn->prepare($sql2);
                $stmt2->execute([$transaction->getAmount(), $transaction->getAccount()->getId()]);

            $conn->commit();
            return $conn->lastInsertId();
        } catch (\PDOException $exception) {
            $conn->rollBack();
            throw new Exception($exception->getMessage());
        }
    }

    public function getByUserAndCategory(int $user_id, int $category = null, $from_date = null, $to_date = null) {
        $parameters = [];
        $parameters[] = $user_id;

        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "SELECT t.id, t.amount, t.account_id, a.name AS account_name, t.category_id,
                tc.name AS category_name, tc.type AS transaction_type, t.note, t.time_event
                FROM transactions AS t
                JOIN accounts AS a ON t.account_id = a.id
                JOIN transaction_categories AS tc ON t.category_id = tc.id
                WHERE a.owner_id = ? ";
        if ($category != null) {
            $parameters[] = $category;
            $sql .= "AND tc.id = ? ";
        }
        if ($from_date != null && $to_date != null) {
            $parameters[] = $from_date;
            $parameters[] = $to_date;
            $sql .= "AND (time_event BETWEEN ? AND ? + INTERVAL 1 DAY ) ";
        }
        $sql .= "ORDER BY time_created DESC;";
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);

        $transactions = [];
        $accountDAO = new AccountDAO();
        $categoryDAO = new CategoryDAO();
        foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $value) {
            $transaction = new Transaction($value->amount,
                                           $accountDAO->getAccountById($value->account_id),
                                           $categoryDAO->getCategoryById($value->category_id, $_SESSION['logged_user']),
                                           $value->note, $value->time_event);
            $transaction->setId($value->id);
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
            $accountDAO = new AccountDAO();
            $categoryDAO = new CategoryDAO();

            $response = $stmt->fetch(\PDO::FETCH_OBJ);
            $transaction = new Transaction($response->amount, $accountDAO->getAccountById($response->account_id),
                $categoryDAO->getCategoryById($response->category_id, $_SESSION['logged_user']), $response->note, $response->time_event);
            $transaction->setId($response->id);
            return $transaction;
        }
        return false;
    }

    public function deleteTransaction(Transaction $transaction) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        try {
            $conn->beginTransaction();

            $sql1 = "DELETE FROM transaction WHERE id = ?;";
            $stmt = $conn->prepare($sql1);
            $stmt->execute([$transaction->getId()]);

            $sql2 = "UPDATE accounts SET current_amount = current_amount + ? WHERE id = ?;";
            $stmt = $conn->prepare($sql2);
            $stmt->execute([$transaction->getAmount(), $transaction->getAccount()->getId()]);

            $conn->commit();
        } catch (\PDOException $exception) {
            $conn->rollBack();
            throw new Exception($exception->getMessage());
        }
    }
}