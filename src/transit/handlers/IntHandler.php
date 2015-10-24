<?php

namespace transit\handlers;

class IntHandler implements GroundHandler {

    public function representation($handler, $obj, $asKey) {
        return $asKey ? '~i' . $obj : $obj;
    }

    public function verboseRepresentation($handler, $obj, $asKey) {
        return $this->representation($handler, $obj, $asKey);
    }

    public function resolve($obj) {
        return (int)$obj;
    }

}