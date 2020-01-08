<?php

namespace exceptions;
class UnauthorizedException extends BaseException {
    public function getStatusCode() {
        return 401;
    }
}