<?php


namespace model\planned_payments;


use model\accounts\Account;
use model\categories\Category;

class PlannedPayment implements \JsonSerializable {
    private $id;
    private $day_for_payment;
    private $amount;
    /** @var Account $account */
    private $account;
    /** @var Category $category */
    private $category;
    private $status;
    private $date_created;

    public function __construct($day_for_payment, $amount, $account, $category) {
        $this->day_for_payment = $day_for_payment;
        $this->amount = $amount;
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

    public function jsonSerialize() {
        return [
            'day_for_payment'=>$this->day_for_payment,
            'amount'=>$this->amount,
            'status'=>$this->status,
            'account'=>$this->account,
            'category'=>$this->category
        ];
    }
}