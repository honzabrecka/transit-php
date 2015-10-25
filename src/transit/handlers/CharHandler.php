<?php

namespace transit\handlers;

use transit\Char;

class CharHandler implements Handler {

    public function tag() {
        return 'c';
    }

    public function type() {
        return Char::class;
    }

    public function representation($obj) {
        return (string)$obj;
    }

    public function resolve($obj) {
        return new Char($obj);
    }

}