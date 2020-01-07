<?php


namespace model\planned_payments;


use model\Connection;

class PlannedPaymentDAO {
    public function create(PlannedPayment $plannedPayment) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();

        $parameters = [];
        $parameters[] = $plannedPayment->getDayForPayment();
        $parameters[] = $plannedPayment->getAmount();
        $parameters[] = $plannedPayment->getAccountId();
        $parameters[] = $plannedPayment->getCategoryId();

        $sql = "INSERT INTO planned_payments(day_for_payment, amount, account_id, category_id, status, date_created) 
                VALUES (?, ?, ?, ?, 1, CURRENT_DATE)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);
    }

    public function getAll($user_id)
    {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();

        $sql = "SELECT pp.day_for_payment, pp.amount, a.name AS account_name, c.name AS category_name, pp.status FROM planned_payments AS pp 
                JOIN accounts AS a ON pp.account_id = a.id
                JOIN transaction_categories AS c ON pp.category_id = c.id
                WHERE a.owner_id = ?
                ORDER BY pp.date_created DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $plannedPayments = [];
        foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $value) {
            $plannedPayment = new PlannedPayment($value->day_for_payment, $value->amount, $value->account_id, $value->category_id);
            $plannedPayments[] = $plannedPayment;
        }
        return $plannedPayments;

    }

    public function changeStatus($planned_payment_id, bool $status) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();

        $sql = "UPDATE planned_payments SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$planned_payment_id, $status]);
    }

    public function getPlannedPaymentById($planned_payment_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();

        $sql = "SELECT pp.day_for_payment, pp.amount, a.name AS account_name, a.owner_id, c.name AS category_name, pp.status FROM planned_payments AS pp 
                    JOIN accounts AS a ON pp.account_id = a.id
                    JOIN transaction_categories AS c ON pp.category_id = c.id
                    WHERE pp.id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$planned_payment_id]);

        if ($stmt->rowCount() == 1) {
            $row = $stmt->fetch(\PDO::FETCH_OBJ);
            $plannedPayment = new PlannedPayment($row->day_for_payment, $row->amount, $row->account_id, $row->category_id);
            return $plannedPayment;
        }
        return false;
    }
}