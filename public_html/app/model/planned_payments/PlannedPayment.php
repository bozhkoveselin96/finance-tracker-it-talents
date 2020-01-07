<?php


namespace model\planned_payments;


class PlannedPayment implements \JsonSerializable {
    private $id;
    private $day_for_payment;
    private $amount;
    private $account_id;
    private $account_name;
    private $category_id;
    private $category_name;
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

    public function setCategoryName($category_name): void
    {
        $this->category_name = $category_name;
    }

    public function setAccountName($account_name): void
    {
        $this->account_name = $account_name;
    }

    public function jsonSerialize() {
        return [
            'day_for_payment'=>$this->day_for_payment,
            'amount'=>$this->amount,
            'status'=>$this->status,
            'account_name'=>$this->account_name,
            'category_name'=>$this->category_name
        ];
    }
}