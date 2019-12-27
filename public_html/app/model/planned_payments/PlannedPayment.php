<?php


namespace model\planned_payments;


class PlannedPayment {
    private $id;
    private $day_for_payment;
    private $amount;
    private $account_id;
    private $category_id;
    private $status;
    private $date_created;

    public function __construct($day_for_payment, $amount, $account_id, $category_id) {
        $this->day_for_payment = $day_for_payment;
        $this->amount = $amount;
        $this->account_id = $account_id;
        $this->category_id = $category_id;
    }

    public function getDayForPayment() {
        return $this->day_for_payment;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getAccountId() {
        return $this->account_id;
    }

    public function getCategoryId() {
        return $this->category_id;
    }
}