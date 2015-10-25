<?php

namespace transit\handlers;

interface Handler {

    function tag();

    function type();

    function representation($obj);

    function resolve($obj);

}