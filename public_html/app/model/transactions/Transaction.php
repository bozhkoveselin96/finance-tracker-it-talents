<?php


namespace model\transactions;


use model\accounts\Account;
use model\categories\Category;

class Transaction implements \JsonSerializable {
    private $id;
    private $amount;
    private $account;
    private $category;
    private $note;
    private $time_created;
    private $time_event;

    public function __construct($amount, Account $account, Category $category, $note, $time_event) {
        $this->amount = $amount;
        $this->account = $account;
        $this->category = $category;
        $this->note = $note;
        $this->time_event = $time_event;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getAccount(){
        return $this->account;
    }


    public function getCategory(){
        return $this->category;
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

    public function jsonSerialize() {
        return [
            'id'=>$this->id,
            'amount' => $this->amount,
            'note' => $this->note,
            'account' => $this->account,
            'category' => $this->category,
            'time_event' => $this->time_event
        ];
    }
}