<?php

namespace transit;

class ReaderCache {

    const CACHE_CODE_DIGITS = 44;
    const BASE_CHAR_INDEX = 48;
    const SUB_STR = '^';

    private $cache = [];

    private $index = 0;

    public function get($value) {
        return $this->cache[$this->codeToIndex($value)];
    }

    public function save($representation, $value, $type, $asKey) {
        if (!$this->cacheable($representation, $value, $type, $asKey)) return $value;

        $this->cache[$this->index++] = $value;
        $this->checkBounds();
        return $value;
    }

    private function cacheable($representation, $value, $type, $asKey) {
        if ($type == gettype('') && $asKey && strlen($representation) > 3) return true;
        if ($type == '__ground' && $asKey && strlen($representation) > 3) return true;
        if (($type == Keyword::class || $type == Symbol::class) && strlen($representation) > 3) return true;
        return false;
    }

    private function checkBounds() {
        if (count($this->cache) === 1937) {
            $this->cache = [];
            $this->index = 0;
        }
    }

    private function codeToIndex($code) {
        return strlen($code) == 2
            ? ord($code[1]) - self::BASE_CHAR_INDEX
            : ((ord($code[1]) - self::BASE_CHAR_INDEX) * self::CACHE_CODE_DIGITS)
                + (ord($code[2]) - self::BASE_CHAR_INDEX);
    }

}
