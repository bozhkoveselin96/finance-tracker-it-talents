<?php


namespace exceptions;


class MethodNotAllowedException extends BaseException {
    public function getStatusCode() {
        return 405;
    }
}