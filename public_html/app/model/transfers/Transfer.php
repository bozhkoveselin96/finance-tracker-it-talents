<?php

namespace model\transfers;

class Transfer implements \JsonSerializable {
    private $id;
    private $amount;
    private $currency;
    private $fromAccount;
    private $toAccount;
    private $time_event;

    public function __construct($amount, $currency, $fromAccount, $toAccount, $time_event) {
        $this->amount = $amount;
        $this->currency = $currency;
        $this->fromAccount = $fromAccount;
        $this->toAccount = $toAccount;
        $this->time_event = $time_event;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function getAmount() {
        return $this->amount;
    }

    public function getCurrency() {
        return $this->currency;
    }

    public function getFromAccount() {
        return $this->fromAccount;
    }

    public function getToAccount() {
        return $this->toAccount;
    }

    public function getTimeEvent() {
        return $this->time_event;
    }

    public function jsonSerialize() {
        return [
            'id'=>$this->id,
            'amount' => $this->amount,
            'currency'=>$this->currency,
            'fromAccount' => $this->fromAccount,
            'toAccount' => $this->toAccount,
            'time_event' => $this->time_event
        ];
    }
}