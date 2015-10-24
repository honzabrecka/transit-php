<?php

namespace transit\handlers;

class MapHandler implements GroundHandler {

    public function representation($handler, $obj, $asKey) {
        $result = ['^ '];

        foreach ($obj as $key => $value) {
            $result[] = $handler($key, true);
            $result[] = $handler($value);
        }

        return $result;
    }

    public function verboseRepresentation($handler, $obj, $asKey) {
        $result = [];

        foreach ($obj as $key => $value) {
            $result[$handler($key, true)] = $handler($value);
        }

        return (object)$result;
    }

    public function resolve($obj) {}

}