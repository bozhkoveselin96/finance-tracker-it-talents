<?php

namespace exceptions;
class NotFoundException extends BaseException {
    public function getStatusCode() {
        return 404;
    }
}