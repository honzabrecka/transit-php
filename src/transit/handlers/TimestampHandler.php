<?php

namespace transit\handlers;

use transit\Timestamp;

class TimestampHandler implements Handler {

    public function tag() {
        return 'm';
    }

    public function type() {
        return Timestamp::class;
    }

    public function representation($obj) {
        return (string)$obj;
    }

    public function resolve($obj) {
        return new Timestamp($obj);
    }

}
