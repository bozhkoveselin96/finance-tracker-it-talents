<?php


namespace model\transactions;


class Transaction implements \JsonSerializable {
    private $id;
    private $amount;
    private $account_id;
    private $category_id;
    private $note;
    private $account_name;
    private $category_name;
    private $category_type;
    private $time_created;
    private $time_event;

    public function __construct($amount, $account_id, $category_id, $note, $time_event) {
        $this->amount = $amount;
        $this->account_id = $account_id;
        $this->category_id = $category_id;
        $this->note = $note;
        $this->time_event = $time_event;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getAccountId(){
        return $this->account_id;
    }


    public function getCategoryId(){
        return $this->category_id;
    }


    public function getNote() {
        return $this->note;
    }


    public function getTimeEvent() {
        return $this->time_event;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function setAccountName($account_name): void
    {
        $this->account_name = $account_name;
    }

    public function setCategoryName($category_name): void
    {
        $this->category_name = $category_name;
    }

    public function setCategoryType($category_type): void
    {
        $this->category_type = $category_type;
    }

    public function jsonSerialize() {
        return [
            'id'=>$this->id,
            'transaction_type' => $this->category_type,
            'amount' => $this->amount,
            'note' => $this->note,
            'account_name' => $this->account_name,
            'category_name' => $this->category_name,
            'time_event' => $this->time_event
        ];
    }
}