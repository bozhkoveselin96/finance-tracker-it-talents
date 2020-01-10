<?php

namespace model\transfers;

use model\Connection;
use model\CurrencyDAO;
use model\transactions\Transaction;

class TransferDAO {
    public function makeTransfer(Transfer $transfer, Transaction $income, Transaction $outcome) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        try {
            $conn->beginTransaction();
            $most_recently_added_ids = [];

            $sql1 = "UPDATE accounts SET current_amount = ROUND(current_amount - ?, 2)
                     WHERE id = ?;";
            $stmt = $conn->prepare($sql1);

            $amount = $transfer->getAmount();
            if ($transfer->getCurrency() != $transfer->getFromAccount()->getCurrency()) {
                $currencyDAO = new CurrencyDAO();
                $amount = $currencyDAO->currencyConverter($transfer->getAmount(), $transfer->getCurrency(), $transfer->getFromAccount()->getCurrency());
            }

            $stmt->execute([$amount, $transfer->getFromAccount()->getId()]);

            $sql2 = "UPDATE accounts SET current_amount = ROUND(current_amount + ?, 2)
                     WHERE id = ?;";
            $stmt = $conn->prepare($sql2);

            $amount = $transfer->getAmount();
            if ($transfer->getCurrency() != $transfer->getToAccount()->getCurrency()) {
                $currencyDAO = new CurrencyDAO();
                $amount = $currencyDAO->currencyConverter($transfer->getAmount(), $transfer->getCurrency(), $transfer->getToAccount()->getCurrency());
            }
            $stmt->execute([$amount, $transfer->getToAccount()->getId()]);

            $sql3 = "INSERT INTO transfers(amount, from_account, to_account, currency, time_event) VALUES (?, ?, ?, ?, ?);";
            $stmt = $conn->prepare($sql3);

            $stmt->execute([
                $transfer->getAmount(),
                $transfer->getFromAccount()->getId(),
                $transfer->getToAccount()->getId(),
                $transfer->getCurrency(),
                $transfer->getTimeEvent()
            ]);
            $most_recently_added_ids["transfer_id"] = $conn->lastInsertId();

            $sql4 = "INSERT INTO transactions(amount, currency, account_id, category_id, note, time_created, time_event)
                     VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?);";
            $stmt = $conn->prepare($sql4);
            $stmt->execute([
                $income->getAmount(),
                $income->getCurrency(),
                $income->getAccount()->getId(),
                $income->getCategory()->getId(),
                $income->getNote(),
                $income->getTimeEvent()
                ]);
            $most_recently_added_ids["transaction_income_id"] = $conn->lastInsertId();

            $sql5 = "INSERT INTO transactions(amount, currency, account_id, category_id, note, time_created, time_event)
                     VALUES (?, ?, ?, ?, ?, CURRENT_TIMESTAMP, ?);";
            $stmt = $conn->prepare($sql5);
            $stmt->execute([
                $outcome->getAmount(),
                $outcome->getCurrency(),
                $outcome->getAccount()->getId(),
                $outcome->getCategory()->getId(),
                $outcome->getNote(),
                $outcome->getTimeEvent()
            ]);
            $most_recently_added_ids["transaction_outcome_id"] = $conn->lastInsertId();
            $conn->commit();
            return $most_recently_added_ids;
        } catch (\PDOException $exception) {
            $conn->rollBack();
            throw new \PDOException($exception->getMessage());
        }
    }
}