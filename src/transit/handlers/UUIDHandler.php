<?php

namespace transit\handlers;

use transit\UUID;

class UUIDHandler implements Handler {

    public function tag() {
        return 'u';
    }

    public function type() {
        return UUID::class;
    }

    public function representation($obj) {
        return (string)$obj;
    }

    public function resolve($obj) {
        return new UUID($obj);
    }

}