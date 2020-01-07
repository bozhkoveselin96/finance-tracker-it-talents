<?php


class ForbiddenException extends BaseException {
    public function getStatusCode() {
        return 403;
    }
}