<?php

namespace transit;

class CMap implements \ArrayAccess {

    private $data = [];

    private $hashes = [];

    private $index = 0;

    public function __construct(array $data) {
        $this->validate($data);
        $key = null;
        $i = 0;

        foreach ($data as $value) {
            $i++ % 2 == 0
                ? $key = $value
                : $this[$key] = $value;
        }
    }

    private function validate($value) {
        if (count($value) % 2 == 1) {
            throw new TransitException('Invalid cmap.');
        }
    }

    public function toArray() {
        return $this->data;
    }

    private function stringify($value) {
        if (gettype($value) === gettype(false)) return $value === true ? '1' : '0';
        return (string)$value;
    }

    public function offsetSet($offset, $value) {
        $hash = $this->hash($offset);

        if (isset($this->hashes[$hash])) {
            $this->data[$this->hashes[$hash] + 1] = $value;
            return;
        }

        $this->data[] = $offset;
        $this->data[] = $value;
        $this->hashes[$hash] = $this->index;
        $this->index = $this->index + 2;
    }

    public function offsetExists($offset) {
        return isset($this->hashes[$this->hash($offset)]);
    }

    public function offsetUnset($offset) {
        $hash = $this->hash($offset);
        $index = $this->hashes[$hash];
        unset($this->hashes[$hash]);
        $this->index = $this->index - 2;
        $beforeIndex = array_slice($this->data, 0, $index);
        $afterIndex = array_slice($this->data, $index + 2);
        $this->data = array_merge($beforeIndex, $afterIndex);
        $this->hashes = array_map(function($i) use ($index) {
            return $i > $index ? $i - 2 : $i;
        }, $this->hashes);
    }

    public function offsetGet($offset) {
        return $this->data[$this->hashes[$this->hash($offset)] + 1];
    }

    private function hash($value) {
        return md5(serialize($value));
    }

}
