<?php


class NotFoundException extends BaseException {
    public function getStatusCode() {
        return 404;
    }
}