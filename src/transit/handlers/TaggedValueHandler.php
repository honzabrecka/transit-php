<?php

namespace transit\handlers;

use transit\TaggedValue;

final class TaggedValueHandler implements Handler {

    private $_tag = '';

    public function __construct($tag) {
        $this->_tag = $tag;
    }

    public function tag() {
        // internal hack to have same arity as interface
        // same value as to representation method is passed
        return func_get_args()[0]->tag;
    }

    public function type() {
        return TaggedValue::class;
    }

    public function representation($obj) {
        return $obj->value;
    }

    public function resolve($obj) {
        return new TaggedValue($this->_tag, $obj);
    }

}
