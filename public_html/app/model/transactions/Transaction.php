<?php


namespace model\transactions;


class Transaction {
    private $id;
    private $amount;
    private $account_id;
    private $category_id;
    private $note;
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
}