<?php


abstract class BaseException extends Exception {
    public abstract function getStatusCode();
}