<?php

namespace transit;

interface Writer {

    function write(Cache $cache, $handlers, $input);

}