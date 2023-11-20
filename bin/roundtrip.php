<?php

error_reporting(E_ERROR | E_PARSE);

require_once __DIR__ . '/../vendor/autoload.php';

use transit\JSONReader;
use transit\JSONWriter;
use transit\Transit;

$input = file_get_contents($argv[1]);
$transit = new Transit(new JSONReader(), new JSONWriter());
$output = $transit->write($transit->read($input));
echo $output;
