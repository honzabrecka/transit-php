<?php

namespace transit\handlers;

class SpecialNumberHandler implements Handler {

    public function tag() {
        return 'z';
    }

    // see FloatHandler
    public function type() {
        return gettype('');
    }

    // see FloatHandler
    public function representation($obj) {}

    public function resolve($obj) {
        $table = ['NaN' => NAN, 'INF' => INF, '-INF' => -INF];
        return $table[$obj];
    }

}
