<?php

namespace transit\handlers;

interface GroundHandler {

    function representation($handler, $obj, $asKey);

    function verboseRepresentation($handler, $obj, $asKey);

    function resolve($obj);

}