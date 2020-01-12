<?php


namespace model\planned_payments;


use model\accounts\Account;
use model\categories\Category;

class PlannedPayment implements \JsonSerializable {
    private $id;
    private $day_for_payment;
    private $amount;
    private $currency;
    /** @var Account $account */
    private $account;
    /** @var Category $category */
    private $category;
    private $status;
    private $date_created;

    public function __construct($day_for_payment, $amount, $currency, $account, $category) {
        $this->day_for_payment = $day_for_payment;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->account = $account;
        $this->category = $category;
    }

    public function getId() {
        return $this->id;
    }

    public function getDayForPayment() {
        return $this->day_for_payment;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getCurrency() {
        return $this->currency;
    }

    public function getAccount() {
        return $this->account;
    }

    public function getCategory() {
        return $this->category;
    }

    public function setStatus($status) {
        $this->status = $status;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setDayForPayment($day_for_payment): void
    {
        $this->day_for_payment = $day_for_payment;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setAmount($amount): void
    {
        $this->amount = $amount;
    }

    public function jsonSerialize() {
        return [
            'id'=>$this->id,
            'day_for_payment' => $this->day_for_payment,
            'amount' => $this->amount,
            'currency' => $this->currency,
            'status' => $this->status,
            'account' => $this->account,
            'category' => $this->category
        ];
    }
}