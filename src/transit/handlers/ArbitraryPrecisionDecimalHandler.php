<?php

namespace transit\handlers;

use transit\ArbitraryPrecisionDecimal;

class ArbitraryPrecisionDecimalHandler implements Handler {

    public function tag() {
        return 'f';
    }

    public function type() {
        return ArbitraryPrecisionDecimal::class;
    }

    public function representation($obj) {
        return (string)$obj;
    }

    public function resolve($obj) {
        return new ArbitraryPrecisionDecimal($obj);
    }

}
