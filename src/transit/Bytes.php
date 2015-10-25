<?php

namespace transit;

class Bytes {

    private $value;

    public function __construct($value) {
        $this->validate($value);
        $this->value = $value;
    }

    private function validate($value) {
        if (!is_string($value)) {
            throw new TransitException('Invalid bytes.');
        }
    }

    public function __toString() {
        return $this->value;
    }

}