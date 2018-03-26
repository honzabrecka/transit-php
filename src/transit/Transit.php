<?php

namespace transit;

use transit\handlers\Handler;
use transit\handlers\QuoteHandler;
use transit\handlers\SpecialNumberHandler;
use transit\handlers\KeywordHandler;
use transit\handlers\SymbolHandler;
use transit\handlers\SetHandler;
use transit\handlers\ListHandler;
use transit\handlers\DateTimeHandler;
use transit\handlers\URIHandler;
use transit\handlers\UUIDHandler;
use transit\handlers\CharHandler;
use transit\handlers\TimestampHandler;
use transit\handlers\ArbitraryPrecisionDecimalHandler;
use transit\handlers\ArbitraryPrecisionIntegerHandler;

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

    public function registerHandler(Handler $handler) {
        $this->readHandlers[$handler->tag()] = $handler;
        $this->writeHandlers[$handler->type()] = $handler;
    }

    private function registerDefaultHandlers() {
        $this->registerHandler(new QuoteHandler());
        $this->registerHandler(new SpecialNumberHandler());
        $this->registerHandler(new KeywordHandler());
        $this->registerHandler(new SymbolHandler());
        $this->registerHandler(new SetHandler());
        $this->registerHandler(new ListHandler());
        $this->registerHandler(new DateTimeHandler());
        $this->registerHandler(new URIHandler());
        $this->registerHandler(new UUIDHandler());
        $this->registerHandler(new CharHandler());
        $this->registerHandler(new TimestampHandler());
        $this->registerHandler(new ArbitraryPrecisionDecimalHandler());
        $this->registerHandler(new ArbitraryPrecisionIntegerHandler());
    }

}
