<?php

namespace transit;

class URI {

    private $value;

    public function __construct($value) {
        $this->validate($value);
        $this->value = $value;
    }

    private function validate($value) {
        if (!is_string($value)) {
            throw new TransitException('Invalid uri.');
        }
    }

    public function __toString() {
        return $this->value;
    }

}