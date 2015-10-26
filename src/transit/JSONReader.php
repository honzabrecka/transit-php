<?php

namespace transit;

use transit\Map;
use transit\Bytes;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class JSONReader implements Reader {

    private $cache;

    private $handlers;

    private $groundHandlers;

    public function __construct() {
        $this->groundHandlers = [
            '_' => function($_) {
                return null;
            },
            '?' => function($obj) {
                $table = ['t' => true, 'f' => false];
                return $table[$obj];
            },
            'i' => function($obj) {
                return (int)$obj;
            },
            'd' => function($obj) {
                return (float)$obj;
            },
            'b' => function($obj) {
                return new Bytes(base64_decode($obj));
            },
            's' => function($obj) {
                return (string)$obj;
            },
            '~' => function($obj) {
                return '~' . $obj;
            },
            '^' => function($obj) {
                return '^' . $obj;
            }
        ];
    }

    public function read(Cache $cache, $handlers, $input) {
        $this->cache = $cache;
        $this->handlers = $handlers;
        return $this->handle($this->parse($input));
    }

    private function parse($input) {
        try {
            return Json::decode($input);
        } catch (JsonException $exception) {
            throw new TransitException('Input is not valid transit.');
        }
    }

    private function handle($input, $asKey = false) {
        if (gettype($input) == gettype(new \stdClass())) {
            throw new TransitException('Input is not valid transit.');
        }

        return is_array($input)
            ? $this->emitComposite($input)
            : $this->emitScalar($input, $asKey);
    }

    private function emitComposite(array $input) {
        if (count($input) == 0) return $input;
        if ($this->isMap($input)) return $this->emitMap($this->rest($input));
        if ($this->isCompositeExtension($input)) return $this->emitCompositeExtension($input);
        return $this->emitArray($input);
    }

    private function isMap(array $input) {
        return is_string($input[0]) && $input[0] == '^ ';
    }

    private function isCompositeExtension(array $input) {
        return is_string($input[0]) && substr($input[0], 0, 2) == '~#';
    }

    private function checkVerboseComposite($input) {
        $countOfEntries = 0;
        $extension = false;

        foreach ($input as $key => $_) {
            if (substr((string)$key, 0, 2) == '~#') $extension = true;
            $countOfEntries++;
        }

        if ($extension && $countOfEntries != 1) {
            throw new TransitException('Input is not valid transit.');
        }

        return $extension;
    }

    private function emitScalar($input, $asKey) {
        return is_string($input)
            ? $this->emitString($input, $asKey)
            : $input;
    }

    private function emitString($input, $asKey) {
        if ($input == '') return $input;
        if ($input[0] == '~') return $this->emitScalarExtension(substr($input, 1), $asKey);
        if ($input[0] == '^') return $this->cache->get($input);
        return $this->cached($input, gettype(''), $asKey);
    }

    private function emitArray(array $input) {
        return array_map(function($item) {
            return $this->handle($item);
        }, $input);
    }

    private function emitMap(array $input) {
        if (count($input) % 2 == 1) {
            throw new TransitException('Input is not valid transit.');
        }

        $result = [];
        $i = 0;

        foreach ($input as $value) {
            $i++ % 2 == 0
                ? $result[] = $this->handle($value, true)
                : $result[] = $this->handle($value);
        }

        return new Map($result);
    }

    private function emitScalarExtension($input, $asKey) {
        $tag = substr($input, 0, 1);
        $value = substr($input, 1);
        return isset($this->groundHandlers[$tag])
            ? $this->groundHandlers[$tag]($value)
            : $this->cached(
                $this->extensionHandler($tag)->resolve($value),
                $this->extensionHandler($tag)->type(),
                $asKey
            );
    }

    private function emitCompositeExtension(array $input) {
        if (count($input) != 2) {
            throw new TransitException('Input is not valid transit.');
        }

        $tag = substr($input[0], 2);

        return $tag == 'cmap'
            ? $this->emitMap($input[1])
            : $this->extensionHandler($tag)->resolve($this->handle($input[1]));
    }

    private function extensionHandler($tag) {
        return isset($this->handlers[$tag])
            ? $this->handlers[$tag]
            : $this->extensionHandlerNotFound($tag);
    }

    private function extensionHandlerNotFound($tag) {
        throw new TransitException('Undefined handler for tag ' . $tag . '.');
    }

    private function cached($value, $type, $asKey) {
        return $asKey
            ? $this->cache->save($value, $type, Cache::READ)
            : $value;
    }

    private function rest(array $input) {
        array_shift($input);
        return $input;
    }

}