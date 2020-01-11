<?php

namespace model\transfers;

use model\transactions\Transaction;

class Transfer implements \JsonSerializable {
    private $id;
    /** @var Transaction $fromTransaction */
    private $fromTransaction;
    /** @var Transaction $toAccount */
    private $toTransaction;

    public function __construct($fromTransaction, $toTransaction) {
        $this->fromTransaction = $fromTransaction;
        $this->toTransaction = $toTransaction;
    }

    public function getId() {
        return $this->id;
    }

    public function setId($id): void {
        $this->id = $id;
    }

    public function getFromTransaction() {
        return $this->fromTransaction;
    }

    public function getToTransaction() {
        return $this->toTransaction;
    }

    public function jsonSerialize() {
        return [
            'id'=>$this->id,
            'fromTransaction' => $this->fromTransaction,
            'toTransaction' => $this->toTransaction,
        ];
    }
}