<?php

namespace transit;

use transit\handlers\ExtensionHandler;
use transit\handlers\KeywordHandler;
use transit\handlers\SymbolHandler;

class Transit {

    private $readHandlers = [];

    private $writeHandlers = [];

    private $reader;

    private $writer;

    public function __construct(Reader $reader, Writer $writer) {
        $this->reader = $reader;
        $this->writer = $writer;
        $this->registerDefaultHandlers();
    }

    public function read($input) {
        return $this->reader->read(new Cache(), $this->readHandlers, $input);
    }

    public function write($input) {
        return $this->writer->write(new Cache(), $this->writeHandlers, $input);
    }

    public function registerHandler(ExtensionHandler $handler) {
        $this->readHandlers[$handler->tag()] = $handler;
        $this->writeHandlers[$handler->type()] = $handler;
    }

    private function registerDefaultHandlers() {
        $this->registerHandler(new KeywordHandler());
        $this->registerHandler(new SymbolHandler());
    }

}