<?php

namespace transit\handlers;

use transit\Set;

class SetHandler implements Handler {

    public function tag() {
        return 'set';
    }

    public function type() {
        return Set::class;
    }

    public function representation($obj) {
        return $obj->toArray();
    }

    public function resolve($obj) {
        return new Set($obj);
    }

}