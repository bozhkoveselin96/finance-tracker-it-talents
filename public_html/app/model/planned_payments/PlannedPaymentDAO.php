<?php


namespace model\planned_payments;


use model\Connection;

class PlannedPaymentDAO {
    public static function create(PlannedPayment $plannedPayment) {
        try {
            $conn = Connection::get();
            $data = [];
            $data[] = $plannedPayment->getDayForPayment();
            $data[] = $plannedPayment->getAmount();
            $data[] = $plannedPayment->getAccountId();
            $data[] = $plannedPayment->getCategoryId();

            $sql = "INSERT INTO planned_payments(day_for_payment, amount, account_id, category_id, status, date_created) 
                VALUES (?, ?, ?, ?, 1, CURRENT_DATE)";
            $stmt = $conn->prepare($sql);
            $stmt->execute($data);
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function getAll($user_id) {
        try {
            $conn = Connection::get();

            $sql = "SELECT pp.day_for_payment, pp.amount, a.name AS account_name, c.name AS category_name, pp.status FROM planned_payments AS pp 
                    JOIN accounts AS a ON pp.account_id = a.id
                    JOIN transaction_categories AS c ON pp.category_id = c.id
                    WHERE a.owner_id = ?
                    ORDER BY pp.date_created DESC";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$user_id]);
            return $stmt->fetchAll(\PDO::FETCH_OBJ);

        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function changeStatus($planned_payment_id, bool $status) {
        try {
            $conn = Connection::get();

            $sql = "UPDATE planned_payments SET status = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$planned_payment_id, $status]);
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    public static function getPlannedPaymentById($planned_payment_id) {
        try {
            $conn = Connection::get();

            $sql = "SELECT pp.day_for_payment, pp.amount, a.name AS account_name, a.owner_id, c.name AS category_name, pp.status FROM planned_payments AS pp 
                    JOIN accounts AS a ON pp.account_id = a.id
                    JOIN transaction_categories AS c ON pp.category_id = c.id
                    WHERE pp.id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->execute([$planned_payment_id]);

            if ($stmt->rowCount() == 1) {
                return $stmt->fetch(\PDO::FETCH_OBJ);
            }
            return false;
        } catch (\PDOException $exception) {
            return false;
        }
    }
}