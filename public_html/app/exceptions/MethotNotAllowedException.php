<?php


namespace exceptions;


class MethotNotAllowedException extends BaseException {
    public function getStatusCode() {
        return 405;
    }
}