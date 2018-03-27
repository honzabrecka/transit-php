<?php

namespace transit;

interface Reader {

    function read(ReaderCache $cache, $handlers, $input);

}
