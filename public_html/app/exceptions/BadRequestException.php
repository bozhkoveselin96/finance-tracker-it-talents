<?php

namespace exceptions;
class BadRequestException extends BaseException {
    public function getStatusCode() {
        return 400;
    }
}