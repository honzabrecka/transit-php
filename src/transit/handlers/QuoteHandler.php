<?php

namespace transit\handlers;

class QuoteHandler implements Handler {

    public function tag() {
        return '\'';
    }

    public function type() {}

    public function representation($obj) {}

    public function resolve($obj) {
        return $obj;
    }

}