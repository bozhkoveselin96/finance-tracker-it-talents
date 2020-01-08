<?php


namespace model\planned_payments;


use model\accounts\AccountDAO;
use model\categories\CategoryDAO;
use model\Connection;

class PlannedPaymentDAO {
    public function create(PlannedPayment $plannedPayment) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();

        $parameters = [];
        $parameters[] = $plannedPayment->getDayForPayment();
        $parameters[] = $plannedPayment->getAmount();
        $parameters[] = $plannedPayment->getAccount()->getId();
        $parameters[] = $plannedPayment->getCategory()->getId();

        $sql = "INSERT INTO planned_payments(day_for_payment, amount, account_id, category_id, status, date_created) 
                VALUES (?, ?, ?, ?, 1, CURRENT_DATE)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($parameters);
    }

    public function getAll($user_id)
    {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();

        $sql = "SELECT pp.day_for_payment, pp.amount, a.name AS account_name, pp.account_id, c.name AS category_name, pp.category_id, pp.status FROM planned_payments AS pp 
                JOIN accounts AS a ON pp.account_id = a.id
                JOIN transaction_categories AS c ON pp.category_id = c.id
                WHERE a.owner_id = ?
                ORDER BY pp.date_created DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$user_id]);
        $plannedPayments = [];
        $categoryDAO = new CategoryDAO();
        $accountDAO = new AccountDAO();
        foreach ($stmt->fetchAll(\PDO::FETCH_OBJ) as $value) {
            $plannedPayment = new PlannedPayment($value->day_for_payment, $value->amount,
                $accountDAO->getAccountById($value->account_id), $categoryDAO->getCategoryById($value->category_id, $_SESSION['logged_user']));
            $plannedPayment->setStatus($value->status);
            $plannedPayments[] = $plannedPayment;
        }
        return $plannedPayments;

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
            $categoryDAO = new CategoryDAO();
            $accountDAO = new AccountDAO();
            $plannedPayment = new PlannedPayment($row->day_for_payment, $row->amount,
                $accountDAO->getAccountById($row->account_id), $categoryDAO->getCategoryById($row->category_id, $_SESSION['logged_user']));
            return $plannedPayment;
        }
        return false;
    }

    public function deletePlannedPayment($planned_payment_id) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "DELETE FROM planned_payment WHERE id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$planned_payment_id]);
    }

    public function editPlannedPayment(PlannedPayment $planned_payment) {
        $instance = Connection::getInstance();
        $conn = $instance->getConn();
        $sql = "UPDATE planned_payment SET day_for_payment = ?, amount = ?, status = ?
                WHERE id = ?;";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$planned_payment->getDayForPayment(),
                        $planned_payment->getCategory(),
                        $planned_payment->getStatus(),
                        $planned_payment->getId()]);
    }
}