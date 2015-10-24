<?php

namespace transit\handlers;

class BoolHandler implements GroundHandler {

    public function representation($handler, $obj, $asKey) {
        return $asKey ? '~?' . ($obj ? 't' : 'f') : $obj;
    }

    public function verboseRepresentation($handler, $obj, $asKey) {
        return $this->representation($handler, $obj, $asKey);
    }

    public function resolve($obj) {
        $table = ['t' => true, 'f' => false];
        return $table[$obj];
    }

}