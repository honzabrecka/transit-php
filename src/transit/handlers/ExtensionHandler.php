<?php

namespace transit\handlers;

interface ExtensionHandler {

    function tag();

    function type();

    function representation($obj);

    function resolve($obj);

}