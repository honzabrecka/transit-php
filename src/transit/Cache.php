<?php

namespace transit;

use transit\Keyword;
use transit\Symbol;

class Cache {

    const WRITE = 1;
    const READ = 2;

    const CACHE_CODE_DIGITS = 44;
    const BASE_CHAR_INDEX = 48;
    const SUB_STR = '^';

    private $cache = [];

    private $index = 0;

    public function save($value, $type, $mode) {
        if (!$this->cacheable($value, $type, $mode)) {
            return $value;
        }

        if ($mode == self::READ) {
            $this->cache[$this->index++] = $value;
            return $value;
        }

        if (isset($this->cache[$value])) {
            return $this->cache[$value];
        }

        $this->cache[$value] = $this->indexToCode($this->index++);
        return $value;
    }

    public function get($value) {
        return $this->cache[$this->codeToIndex($value)];
    }

    private function cacheable($value, $type, $mode) {
        $cacheableTypes = [
            gettype('') => 3,
            Keyword::class => $mode == self::READ ? 1 : 3,
            Symbol::class => $mode == self::READ ? 1 : 3
        ];
        return isset($cacheableTypes[$type]) && strlen($value) > $cacheableTypes[$type];
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
