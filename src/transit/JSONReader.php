<?php

namespace transit;

use transit\handlers\NullHandler;
use transit\handlers\BoolHandler;
use transit\handlers\IntHandler;
use transit\handlers\FloatHandler;
use transit\handlers\StringHandler;
use transit\handlers\QuoteHandler;
use transit\handlers\SpecialNumberHandler;
use Nette\Utils\Json;
use Nette\Utils\JsonException;

class JSONReader implements Reader {

    private $verbose;

    private $cache;

    private $handlers;

    public function __construct($verbose = false) {
        $this->verbose = $verbose;
    }

    public function read(Cache $cache, $handlers, $input) {
        $this->cache = $cache;
        $this->handlers = array_merge($handlers, $this->groundHandlers());
        return $this->handle($this->parse($input));
    }

    private function groundHandlers() {
        return [
            '_' => new NullHandler(),
            '?' => new BoolHandler(),
            'i' => new IntHandler(),
            'd' => new FloatHandler(),
            's' => new StringHandler(),
            'z' => new SpecialNumberHandler(),
            '\'' => new QuoteHandler()
        ];
    }

    private function parse($input) {
        try {
            return Json::decode($input);
        } catch (JsonException $exception) {
            throw new TransitException('Input is not valid transit.');
        }
    }

    private function handle($input, $asKey = false) {
        if (!$this->verbose && gettype($input) == gettype(new \stdClass())) {
            throw new TransitException('Input is not valid transit.');
        }

        $compositeTypes = [
            gettype([]) => [$this, 'emitComposite'],
            gettype(new \stdClass()) => [$this, 'emitVerboseComposite']
        ];
        $type = gettype($input);

        return isset($compositeTypes[$type])
            ? $compositeTypes[$type]($input)
            : $this->emitScalar($input, $asKey);
    }

    private function emitComposite(array $input) {
        if (count($input) == 0) return $input;
        if (!$this->verbose && $this->isMap($input)) return $this->emitMap($input);
        if (!$this->verbose && $this->isCompositeExtension($input)) return $this->emitCompositeExtension($input);
        return $this->emitArray($input);
    }

    private function emitVerboseComposite($input) {
        $extension = $this->checkVerboseComposite($input);
        $result = [];

        foreach ($input as $key => $value) {
            if (!$extension) {
                $result[$this->handle($key, true)] = $this->handle($value);
            }
        }

        return $extension
            ? $this->emitCompositeExtension([$key, $value])
            : (object)$result;
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
        if ($input[0] == '~') return $this->emitScalarExtension(substr($input, 1));
        if ($input[0] == '^') return $this->cache->get($input);
        return $this->cached($input, gettype(''), $asKey);
    }

    private function emitArray(array $input) {
        return array_map(function($item) {
            return $this->handle($item);
        }, $input);
    }

    private function emitMap(array $input) {
        if (count($input) % 2 == 0) {
            throw new TransitException('Input is not valid transit.');
        }

        array_shift($input);

        $result = [];
        $i = 0;
        $key = null;

        foreach ($input as $value) {
            $i++ % 2 == 0
                ? $key = $this->handle($value, true)
                : $result[$key] = $this->handle($value);
        }

        return (object)$result;
    }

    private function emitScalarExtension($input) {
        $tag = substr($input, 0, 1);
        $value = substr($input, 1);
        $quotes = ['~' => true, '^' => true];
        return isset($quotes[$tag])
            ? $input
            : $this->extensionHandler($tag)->resolve($value);
    }

    private function emitCompositeExtension(array $input) {
        if (count($input) != 2) {
            throw new TransitException('Input is not valid transit.');
        }

        return $this->extensionHandler(substr($input[0], 2))->resolve($this->handle($input[1]));
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

}