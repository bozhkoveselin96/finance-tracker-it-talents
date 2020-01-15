<?php

namespace model\transfers;

use model\Connection;
use model\CurrencyDAO;
use model\transactions\Transaction;
use model\transactions\TransactionDAO;

class TransferDAO {
    public function makeTransfer(Transfer $transfer) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        try {
            $conn->beginTransaction();

            $sql1 = "UPDATE accounts SET current_amount = ROUND(current_amount - ?, 2)
                     WHERE id = ?;";
            $stmt = $conn->prepare($sql1);

            $amount = $transfer->getFromTransaction()->getAmount();
            if ($transfer->getFromTransaction()->getCurrency() != $transfer->getFromTransaction()->getAccount()->getCurrency()) {
                $currencyDAO = new CurrencyDAO();
                $amount = $currencyDAO->currencyConverter(
                    $transfer->getFromTransaction()->getAmount(),
                    $transfer->getFromTransaction()->getCurrency(),
                    $transfer->getFromTransaction()->getAccount()->getCurrency()
                );
            }
            $transfer->getFromTransaction()->getAccount()->setCurrentAmount(round($transfer->getFromTransaction()->getAccount()->getCurrentAmount() - $amount, 2));

            $stmt->execute([$amount, $transfer->getFromTransaction()->getAccount()->getId()]);

            $sql2 = "UPDATE accounts SET current_amount = ROUND(current_amount + ?, 2)
                     WHERE id = ?;";
            $stmt = $conn->prepare($sql2);

            $amount = $transfer->getToTransaction()->getAmount();
            if ($transfer->getToTransaction()->getAmount() != $transfer->getToTransaction()->getAccount()->getCurrency()) {
                $currencyDAO = new CurrencyDAO();
                $amount = $currencyDAO->currencyConverter(
                    $transfer->getToTransaction()->getAmount(),
                    $transfer->getToTransaction()->getCurrency(),
                    $transfer->getToTransaction()->getAccount()->getCurrency());
            }
            $transfer->getToTransaction()->getAccount()->setCurrentAmount(round($transfer->getToTransaction()->getAccount()->getCurrentAmount() + $amount, 2));

            $stmt->execute([$amount, $transfer->getToTransaction()->getAccount()->getId()]);

            $sql4 = "INSERT INTO transactions(amount, currency, account_id, category_id, note, time_created, time_event)
                     VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?);";
            $stmt = $conn->prepare($sql4);
            $stmt->execute([
                $transfer->getToTransaction()->getAmount(),
                $transfer->getToTransaction()->getCurrency(),
                $transfer->getToTransaction()->getAccount()->getId(),
                $transfer->getToTransaction()->getCategory()->getId(),
                $transfer->getToTransaction()->getNote(),
                $transfer->getToTransaction()->getTimeEvent()
            ]);
            $transfer->getToTransaction()->setId($conn->lastInsertId());

            $sql5 = "INSERT INTO transactions(amount, currency, account_id, category_id, note, time_created, time_event)
                     VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?);";
            $stmt = $conn->prepare($sql5);
            $stmt->execute([
                $transfer->getFromTransaction()->getAmount(),
                $transfer->getFromTransaction()->getCurrency(),
                $transfer->getFromTransaction()->getAccount()->getId(),
                $transfer->getFromTransaction()->getCategory()->getId(),
                $transfer->getFromTransaction()->getNote(),
                $transfer->getFromTransaction()->getTimeEvent()
            ]);
            $transfer->getFromTransaction()->setId($conn->lastInsertId());

            $sql3 = "INSERT INTO transfers(from_transaction, to_transaction) VALUES (?, ?);";
            $stmt = $conn->prepare($sql3);

            $stmt->execute([
                $transfer->getFromTransaction()->getId(),
                $transfer->getToTransaction()->getId()
            ]);

            $conn->commit();
        } catch (\PDOException $exception) {
            $conn->rollBack();
            throw new \PDOException($exception->getMessage());
        }
    }

    public function getTransferByTransaction(Transaction $transaction) {
        $conn = Connection::getInstance()->getConn();
        $sql = "SELECT from_transaction, to_transaction FROM transfers WHERE from_transaction = ? OR to_transaction = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$transaction->getId(), $transaction->getId()]);
        $data = $stmt->fetch(\PDO::FETCH_OBJ);
        if ($data) {
            $transactionDAO = new TransactionDAO();
            return new Transfer(
                $transactionDAO->getTransactionById($data->from_transaction),
                $transactionDAO->getTransactionById($data->to_transaction)
            );
        }
        return false;
    }
}