<?php

namespace transit;

use Nette\Utils\Json;

class JSONWriter implements Writer {

    private $cache;

    private $handlers;

    private $groundHandlers;

    public function __construct() {
        $this->groundHandlers = [
            gettype('') => function($obj, $asKey) {
                $bad = ['~' => true, '^' => true];
                return strlen($obj) > 0 && isset($bad[$obj[0]]) ? '~' . $obj : $obj;
            },
            gettype(1) => function($obj, $asKey) {
                return $asKey ? '~i' . $obj : $obj;
            },
            gettype(1.1) => function($obj, $asKey) {
                if (is_nan($obj)) return '~zNaN';
                if ($obj == INF) return '~zINF';
                if ($obj == -INF) return '~z-INF';
                return $asKey ? '~d' . $obj : $obj;
            },
            gettype(true) => function($obj, $asKey) {
                return $asKey ? '~?' . ($obj ? 't' : 'f') : $obj;
            },
            gettype(null) => function($obj, $asKey) {
                return $asKey ? '~_' : $obj;
            },
            gettype([]) => function($obj, $_) {
                $result = [];

                foreach ($obj as $value) {
                    $result[] = $this->handle($value);
                }

                return $result;
            },
            Bytes::class => function($obj, $_) {
                return '~b' . base64_encode((string)$obj);
            },
            Map::class => function($obj, $_) {
                $result = [];
                $handledValue = null;
                $compositeKey = false;
                $i = 0;
                $asKey = false;

                foreach ($obj->toArray() as $value) {
                    $asKey = $i++ % 2 == 0;
                    $handledValue = $this->handle($value, $asKey);
                    if ($asKey && !$compositeKey && is_array($handledValue)) $compositeKey = true;
                    $result[] = $handledValue;
                }

                if ($compositeKey) {
                    return ['~#cmap', $result];
                }

                array_unshift($result, '^ ');
                return $result;
            },
        ];
    }

    public function write(Cache $cache, $handlers, $input) {
        $this->cache = $cache;
        $this->handlers = $handlers;
        return Json::encode($this->handleTop($input));
    }

    private function handleTop($input) {
        $result = $this->handle($input);
        return is_array($result)
            ? $result
            : ['~#\'', $result];
    }

    private function handle($input, $asKey = false) {
        $type = $this->type($input);
        return isset($this->groundHandlers[$type])
            ? $this->cached($this->groundHandlers[$type]($input, $asKey), $type, $asKey)
            : $this->handleExtension($type, $input, $asKey);
    }

    private function handleExtension($type, $input, $asKey = false) {
        $handler = $this->extensionHandler($type);
        $tag = $handler->tag();
        $result = $this->handle($handler->representation($input));
        return $this->isScalarExtension($tag)
            ? $this->cached('~' . $tag . $result, $type, $asKey)
            : ['~#' . $tag, $result];
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