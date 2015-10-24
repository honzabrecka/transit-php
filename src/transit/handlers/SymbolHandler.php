<?php

namespace transit\handlers;

use transit\types\Symbol;

class SymbolHandler implements ExtensionHandler {

    public function tag() {
        return '$';
    }

    public function type() {
        return Symbol::class;
    }

    public function representation($obj) {
        return (string)$obj;
    }

    public function resolve($obj) {
        return new Symbol($obj);
    }

}