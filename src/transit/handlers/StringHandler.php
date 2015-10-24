<?php

namespace transit\handlers;

class StringHandler implements GroundHandler {

    public function representation($handler, $obj, $asKey) {
        $bad = ['~' => true, '^' => true];
        return strlen($obj) > 0 && isset($bad[$obj[0]])
            ? '~' . $obj
            : $obj;
    }

    public function verboseRepresentation($handler, $obj, $asKey) {
        return $this->representation($handler, $obj, $asKey);
    }

    public function resolve($obj) {
        return (string)$obj;
    }

}