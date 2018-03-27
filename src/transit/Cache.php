<?php

namespace transit;

class Cache {

    const CACHE_CODE_DIGITS = 44;
    const BASE_CHAR_INDEX = 48;
    const SUB_STR = '^';

    private $cache = [];

    private $index = 0;

    public function getByCode($value) {
        return $this->cache[$this->codeToIndex($value)];
    }

    public function getByIndex($value) {
        return $this->cache[$this->codeToIndex($value)];
    }

    public function saveRead($representation, $value, $type, $asKey) {
        if (!$this->cacheable($representation, NULL, $type, $asKey)) return $value;

        $this->cache[$this->index++] = $value;
        $this->checkBounds();
        return $value;
    }

    public function saveWrite($value, $type, $asKey) {
        if (!$this->cacheable($value, NULL, $type, $asKey)) return $value;
        if (isset($this->cache[$value])) return $this->cache[$value];

        $this->cache[$value] = $this->indexToCode($this->index++);
        $this->checkBounds();
        return $value;
    }

    private function cacheable($representation, $value, $type, $asKey) {
        if ($asKey && strlen($representation) > 3) return true;
        if (($type == Keyword::class || $type == Symbol::class) && strlen($representation) > 3) return true;
        return false;
    }

    private function checkBounds() {
        if (count($this->cache) === 1937) {
            $this->cache = [];
            $this->index = 0;
        }
    }

    private function indexToCode($index) {
        $hi = (int)($index / self::CACHE_CODE_DIGITS);
        $lo = (int)($index % self::CACHE_CODE_DIGITS);
        return $hi == 0
            ? self::SUB_STR . chr($lo + self::BASE_CHAR_INDEX)
            : self::SUB_STR . chr($hi + self::BASE_CHAR_INDEX) . chr($lo + self::BASE_CHAR_INDEX);
    }

    private function codeToIndex($code) {
        return strlen($code) == 2
            ? ord($code[1]) - self::BASE_CHAR_INDEX
            : ((ord($code[1]) - self::BASE_CHAR_INDEX) * self::CACHE_CODE_DIGITS)
                + (ord($code[2]) - self::BASE_CHAR_INDEX);
    }

}
