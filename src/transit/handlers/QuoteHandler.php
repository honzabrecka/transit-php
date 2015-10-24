<?php

namespace transit\handlers;

class QuoteHandler implements GroundHandler {

    public function representation($handler, $obj, $asKey) {
        return ['~#\'', $obj];
    }

    public function verboseRepresentation($handler, $obj, $asKey) {
        return (object)['~#\'' => $obj];
    }

    public function resolve($obj) {
        return $obj;
    }

}