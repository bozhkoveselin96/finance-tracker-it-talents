<?php


namespace controller;


class ResponseBody implements \JsonSerializable
{

    private $msg;
    private $data;

    /**
     * ResponseBody constructor.
     * @param $msg
     * @param $body
     */
    public function __construct($msg, $body)
    {
        $this->msg = $msg;
        $this->data = $body;
    }


    /**
     * @inheritDoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}