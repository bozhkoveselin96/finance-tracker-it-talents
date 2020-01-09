<?php


namespace controller;


class ResponseBody implements \JsonSerializable {
    private $msg;
    private $data;

    public function __construct($msg, $body) {
        $this->msg = $msg;
        $this->data = $body;
    }

    public function jsonSerialize() {
        return get_object_vars($this);
    }
}