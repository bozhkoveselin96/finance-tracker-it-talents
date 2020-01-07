<?php

namespace model\budgets;

class Budget {
    private $id;
    private $category_id;
    private $amount;
    private $owner_id;
    private $from_date;
    private $to_date;
    private $date_created;
    private $progress;

    public function __construct($category_id, $amount, $owner_id, $from_date, $to_date) {
        $this->category_id = $category_id;
        $this->amount = $amount;
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

    public function getCategoryId() {
        return $this->category_id;
    }

    public function getFromDate() {
        return $this->from_date;
    }

    public function getToDate() {
        return $this->to_date;
    }

    public function setCategoryId($category_id) {
        $this->category_id = $category_id;
    }

    public function getProgress() {
        return $this->progress;
    }

    public function setProgress($progress) {
        $this->progress = $progress;
    }

    public function setId($id) {
        $this->id = $id;
    }
}