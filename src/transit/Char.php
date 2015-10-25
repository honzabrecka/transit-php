<?php

namespace transit;

class Char {

    private $value;

    public function __construct($value) {
        $this->validate($value);
        $this->value = $value;
    }

    private function validate($value) {
        if (!is_string($value) || strlen($value) != 1) {
            throw new TransitException('Invalid char.');
        }
    }

    public function __toString() {
        return $this->value;
    }

}