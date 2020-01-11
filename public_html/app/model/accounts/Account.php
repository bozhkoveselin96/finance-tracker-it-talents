<?php


namespace model\accounts;


use model\users\User;

class Account implements \JsonSerializable {
    private $id;
    private $name;
    private $current_amount;
    private $owner_id;
    private $currency;

    public function __construct($name, $current_amount, $currency, $owner_id) {
        $this->name = $name;
        $this->current_amount = $current_amount;
        $this->currency = $currency;
        $this->owner_id = $owner_id;
    }

    public function getName() {
        return $this->name;
    }

    public function getCurrentAmount() {
        return $this->current_amount;
    }

    public function getCurrency() {
        return $this->currency;
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

    public function setName($name): void {
        $this->name = $name;
    }

    /**
     * @param mixed $current_amount
     */
    public function setCurrentAmount($current_amount): void
    {
        $this->current_amount = $current_amount;
    }

    public function jsonSerialize() {
        return [
            'id' =>$this->id,
            'name' => $this->name,
            'current_amount' => $this->current_amount,
            'currency' => $this->currency
        ];
    }
}