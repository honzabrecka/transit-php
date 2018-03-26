<?php

namespace transit\handlers;

use transit\ArbitraryPrecisionInteger;

class ArbitraryPrecisionIntegerHandler implements Handler {

    public function tag() {
        return 'n';
    }

    public function type() {
        return ArbitraryPrecisionInteger::class;
    }

    public function representation($obj) {
        return (string)$obj;
    }

    public function resolve($obj) {
        return new ArbitraryPrecisionInteger($obj);
    }

}
