<?php

namespace transit;

interface Reader {

    function read(Cache $cache, $handlers, $input);

}
