<?php

namespace transit\handlers;

use transit\types\Keyword;

class KeywordHandler implements ExtensionHandler {

    public function tag() {
        return ':';
    }

    public function type() {
        return Keyword::class;
    }

    public function representation($obj) {
        return (string)$obj;
    }

    public function resolve($obj) {
        return new Keyword($obj);
    }

}