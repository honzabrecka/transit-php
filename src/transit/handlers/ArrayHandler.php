<?php

namespace transit\handlers;

class ArrayHandler implements GroundHandler {

    public function representation($handler, $obj, $asKey) {
        return array_map(function($value) use ($handler) {
            return $handler($value);
        }, $obj);
    }

    public function verboseRepresentation($handler, $obj, $asKey) {
        return $this->representation($handler, $obj, $asKey);
    }

    public function resolve($obj) {}

}