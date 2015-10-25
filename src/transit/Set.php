<?php

namespace transit;

class Set {

    private $data = [];

    private $hashes = [];

    private $index = 0;

    public function __construct(array $data) {
        foreach ($data as $value) {
            $this->add($value);
        }
    }

    public function add($value) {
        $hash = $this->hash($value);
        
        if (isset($this->hashes[$hash])) {
            throw new TransitException('Set can not contain duplicated values.');
        }

        $this->data[$this->index] = $value;
        $this->hashes[$hash] = $this->index;
        $this->index++;
    }

    public function remove($value) {
        $hash = $this->hash($value);
        $index = $this->hashes[$hash];
        unset($this->hashes[$hash]);
        $this->index--;
        $beforeIndex = array_slice($this->data, 0, $index);
        $afterIndex = array_slice($this->data, $index + 1);
        $this->data = array_merge($beforeIndex, $afterIndex);
        $this->hashes = array_map(function($i) use ($index) {
            return $i > $index ? $i - 1 : $i;
        }, $this->hashes);
    }

    public function contains($value) {
        return isset($this->hashes[$this->hash($value)]);
    }

    public function toArray() {
        return $this->data;
    }

    private function hash($value) {
        return md5(serialize($value));
    }

}