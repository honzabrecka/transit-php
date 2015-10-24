<?php

namespace transit\handlers;

class NullHandler implements GroundHandler {

    public function representation($handler, $obj, $asKey) {
        return $asKey ? '~_' : $obj;
    }

    public function verboseRepresentation($handler, $obj, $asKey) {
        return $this->representation($handler, $obj, $asKey);
    }

    public function resolve($obj) {
        return null;
    }

}