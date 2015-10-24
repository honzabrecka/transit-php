<?php

namespace transit;

use transit\handlers\StringHandler;
use transit\handlers\IntHandler;
use transit\handlers\FloatHandler;
use transit\handlers\BoolHandler;
use transit\handlers\NullHandler;
use transit\handlers\ArrayHandler;
use transit\handlers\MapHandler;
use transit\handlers\QuoteHandler;
use Nette\Utils\Json;

class JSONWriter implements Writer {

    private $verbose;

    private $cache;

    private $handlers;

    public function __construct($verbose = false) {
        $this->verbose = $verbose;
    }

    public function write(Cache $cache, $handlers, $input) {
        $this->cache = $cache;
        $this->handlers = $handlers;
        return Json::encode($this->handleTop($input));
    }

    private function groundHandlers() {
        return [
            gettype('') => new StringHandler(),
            gettype(1) => new IntHandler(),
            gettype(1.1) => new FloatHandler(),
            gettype(true) => new BoolHandler(),
            gettype(null) => new NullHandler(),
            gettype([]) => new ArrayHandler(),
            \stdClass::class => new MapHandler(),
            '\'' => new QuoteHandler()
        ];
    }

    private function handleTop($input) {
        $result = $this->handle($input);
        $compositeTypes = [gettype([]) => true, gettype(new \stdClass()) => true];
        return isset($compositeTypes[gettype($result)])
            ? $result
            : $this->handleGround('\'', $result);
    }

    private function handle($input, $asKey = false) {
        $type = $this->type($input);
        return isset($this->groundHandlers()[$type])
            ? $this->cached($this->handleGround($type, $input, $asKey), $type, $asKey)
            : $this->handleExtension($type, $input, $asKey);
    }

    private function handleGround($type, $input, $asKey = false) {
        $handler = function($value, $asKey = false) {
            return $this->handle($value, $asKey);
        };

        return $this->verbose
            ? $this->groundHandlers()[$type]->verboseRepresentation($handler, $input, $asKey)
            : $this->groundHandlers()[$type]->representation($handler, $input, $asKey);
    }

    private function handleExtension($type, $input, $asKey = false) {
        $handler = $this->extensionHandler($type);
        $tag = $handler->tag();
        $result = $this->handle($handler->representation($input));
        return $this->isScalarExtension($tag)
            ? $this->cached('~' . $tag . $result, $type, $asKey)
            : ($this->verbose ? (object)['~#' . $tag => $result] : ['~#' . $tag, $result]);
    }

    private function type($input) {
        $type = gettype($input);
        return $type == gettype(new \stdClass())
            ? get_class($input)
            : $type;
    }

    private function isScalarExtension($tag) {
        return strlen($tag) == 1;
    }

    private function extensionHandler($type) {
        return isset($this->handlers[$type])
            ? $this->handlers[$type]
            : $this->extensionHandlerNotFound($type);
    }

    private function extensionHandlerNotFound($type) {
        throw new TransitException('Undefined handler for type ' . $type . '.');
    }

    private function cached($value, $type, $asKey) {
        return $asKey
            ? $this->cache->save($value, $type, Cache::WRITE)
            : $value;
    }

}