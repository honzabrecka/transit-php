<?php

namespace transit\handlers;

class SpecialNumberHandler implements GroundHandler {

    // see FloatHandler
    public function representation($handler, $obj, $asKey) {}

    // see FloatHandler
    public function verboseRepresentation($handler, $obj, $asKey) {}

    public function resolve($obj) {
        $table = ['NaN' => NAN, 'INF' => INF, '-INF' => -INF];
        return $table[$obj];
    }

}