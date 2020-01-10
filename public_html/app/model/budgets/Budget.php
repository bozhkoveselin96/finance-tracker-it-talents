<?php

namespace model\budgets;

use model\categories\Category;

class Budget implements \JsonSerializable {
    private $id;
    /** @var Category $category */
    private $category;
    private $amount;
    private $owner_id;
    private $from_date;
    private $to_date;
    private $currency;
    private $progress;

    public function __construct(Category $category, $amount, $currency, $owner_id, $from_date, $to_date) {
        $this->category = $category;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->owner_id = $owner_id;
        $this->from_date = $from_date;
        $this->to_date = $to_date;
    }

    public function getId() {
        return $this->id;
    }

    public function getOwnerId() {
        return $this->owner_id;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getCategory() {
        return $this->category;
    }

    public function getFromDate() {
        return $this->from_date;
    }

    public function getToDate() {
        return $this->to_date;
    }

    public function getCurrency()
    {
        return $this->currency;
    }

    public function setProgress($progress) {
        $this->progress = $progress;
    }

    public function setId($id) {
        $this->id = $id;
    }

    public function jsonSerialize(){
        return [
            'id'=>$this->id,
            'category'=>$this->category,
            'amount'=>$this->amount,
            'currency'=>$this->currency,
            'budget_status'=>$this->progress,
            'from_date'=>$this->from_date,
            'to_date'=>$this->to_date
        ];
    }
}