<?php

namespace transit\handlers;

class FloatHandler implements GroundHandler {

    public function representation($handler, $obj, $asKey) {
        if (is_nan($obj)) return '~zNaN';
        if ($obj == INF) return '~zINF';
        if ($obj == -INF) return '~z-INF';
        return $asKey ? '~d' . $obj : $obj;
    }

    public function verboseRepresentation($handler, $obj, $asKey) {
        return $this->representation($handler, $obj, $asKey);
    }

    public function resolve($obj) {
        return (float)$obj;
    }

}