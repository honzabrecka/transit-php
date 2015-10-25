<?php

namespace transit\handlers;

class ListHandler implements Handler {

    public function tag() {
        return 'list';
    }

    public function type() {
        return \SplDoublyLinkedList::class;
    }

    public function representation($obj) {
        return iterator_to_array($obj);
    }

    public function resolve($obj) {
        $result = new \SplDoublyLinkedList();

        foreach ($obj as $value) {
            $result->push($value);
        }

        return $result;
    }

}