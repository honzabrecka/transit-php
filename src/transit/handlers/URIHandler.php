<?php

namespace transit\handlers;

use transit\URI;

class URIHandler implements Handler {

    public function tag() {
        return 'r';
    }

    public function type() {
        return URI::class;
    }

    public function representation($obj) {
        return (string)$obj;
    }

    public function resolve($obj) {
        return new URI($obj);
    }

}