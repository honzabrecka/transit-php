<?php

namespace transit\types;

use transit\TransitException;

class Keyword {

    private $id;

    public function __construct($id) {
        $this->validate($id);
        $this->id = $id;
    }

    private function validate($value) {
        if (!is_string($value) || $value == '') {
            throw new TransitException('Invalid keyword.');
        }
    }

    public function __toString() {
        return $this->id;
    }

}