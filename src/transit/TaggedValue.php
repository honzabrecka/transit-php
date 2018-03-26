<?php

namespace transit;

final class TaggedValue {

    public $tag;

    public $value;

    public function __construct($tag, $value) {
        $this->tag = $tag;
        $this->value = $value;
    }

    public function __toString() {
        return $tag . '=' . (string)$this->value;
    }

}
