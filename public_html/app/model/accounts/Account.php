<?php


namespace model\accounts;


class Account {
    private $id;
    private $name;
    private $current_amount;
    private $owner_id;
    private $date_created;

    public function __construct($name, $current_amount, $owner_id) {
        $this->name = $name;
        $this->current_amount = $current_amount;
        $this->owner_id = $owner_id;
    }

    public function getName() {
        return $this->name;
    }

    public function getCurrentAmount() {
        return $this->current_amount;
    }

    public function getOwnerId() {
        return $this->owner_id;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = $id;
    }
}