<?php


namespace model\transactions;


use controller\CurrencyController;
use controller\TransferController;
use model\accounts\AccountDAO;
use model\categories\CategoryDAO;
use model\Connection;
use model\CurrencyDAO;

class TransactionDAO {
    public function create(Transaction $transaction) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        try {
            $conn->beginTransaction();
                $parameters = [];
                $parameters[] = $transaction->getAmount();
                $parameters[] = $transaction->getCurrency();
                $parameters[] = $transaction->getAccount()->getId();
                $parameters[] = $transaction->getCategory()->getId();
                $parameters[] = $transaction->getNote();
                $parameters[] = $transaction->getTimeEvent();

                $sql = "INSERT INTO transactions(amount, currency, account_id, category_id, note, time_created, time_event)
                        VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?);";
                $stmt = $conn->prepare($sql);
                $stmt->execute($parameters);

                $accountCurrency = $transaction->getAccount()->getCurrency();
                $transactionCurrency = $transaction->getCurrency();
                $updateAccountAmount = $transaction->getAmount();
                if ($accountCurrency != $transactionCurrency) {
                    $currencyDAO = new CurrencyDAO();
                    $updateAccountAmount = $currencyDAO->currencyConverter($transaction->getAmount(), $transactionCurrency, $accountCurrency);
                }

                $sql2 = "UPDATE accounts SET current_amount = ROUND(current_amount + ?, 2) WHERE id = ?";
                if ($transaction->getCategory()->getType() == 0) {
                    $sql2 = "UPDATE accounts SET current_amount = ROUND(current_amount - ?, 2) WHERE id = ?";
                }

                $stmt2 = $conn->prepare($sql2);
                $stmt2->execute([$updateAccountAmount, $transaction->getAccount()->getId()]);

            $conn->commit();
        } catch (\PDOException $exception) {
            $conn->rollBack();
            throw new \PDOException($exception->getMessage());
        }
    }

    public function getByUserAndCategory(int $user_id, int $category = null, $from_date = null, $to_date = null) {
        $parameters = [];
        $parameters[] = $user_id;

        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "SELECT t.id, t.amount, t.account_id, t.currency, t.category_id, tc.type AS transaction_type, t.note, t.time_event
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
                                           $value->currency,
                                           $categoryDAO->getCategoryById($value->category_id, $_SESSION['logged_user']),
                                           $value->note, $value->time_event);
            $transaction->setId($value->id);
            if ($transaction->getCategory()->getType() == null) {
                $transferController = new TransferController();
                $type = $transferController->checkTransactionType($transaction);
                $transaction->getCategory()->setType($type);
            }
            $transactions[] = $transaction;
        }
        return $transactions;
    }

    public function getTransactionById(int $transaction_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "SELECT id, amount, account_id, currency, category_id, note, time_event FROM transactions WHERE id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$transaction_id]);
        if ($stmt->rowCount() == 1) {
            $accountDAO = new AccountDAO();
            $categoryDAO = new CategoryDAO();
            $response = $stmt->fetch(\PDO::FETCH_OBJ);
            $transaction = new Transaction($response->amount, $accountDAO->getAccountById($response->account_id), $response->currency,
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

            $sql1 = "DELETE FROM transactions WHERE id = ?;";
            $stmt = $conn->prepare($sql1);
            $stmt->execute([$transaction->getId()]);

            $sign = '+';
            if ($transaction->getCategory()->getType() == CATEGORY_INCOME) {
                $sign = '-';
            }

            $sql2 = "UPDATE accounts SET current_amount = ROUND(current_amount $sign ?, 2) WHERE id = ?;";
            $stmt = $conn->prepare($sql2);

            $amount = $transaction->getAmount();
            $currencyDAO = new CurrencyDAO();
            if ($transaction->getCurrency() != $transaction->getAccount()->getCurrency()) {
                $amount = $currencyDAO->currencyConverter($amount, $transaction->getCurrency(), $transaction->getAccount()->getCurrency());
            }

            $stmt->execute([$amount, $transaction->getAccount()->getId()]);

            $conn->commit();
        } catch (\PDOException $exception) {
            $conn->rollBack();
            throw new \PDOException($exception->getMessage());
        }
    }
}