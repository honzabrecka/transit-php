# transit-php [![Build Status](https://travis-ci.org/honzabrecka/transit-php.svg?branch=master)](https://travis-ci.org/honzabrecka/transit-php)

Transit is a data format and a set of libraries for conveying values between applications written in different languages. This library provides support for marshalling Transit data to/from PHP. Unlike the Java and Clojure implementations it relies on the non-streaming JSON parsing mechanism of the host PHP environment.

* [Rationale](http://blog.cognitect.com/blog/2014/7/22/transit)
* [Specification](http://github.com/cognitect/transit-format)

This implementation's major.minor version number corresponds to the version of
the Transit specification it supports.

_NOTE: Transit is a work in progress and may evolve based on feedback.
As a result, while Transit is a great option for transferring data
between applications, it should not yet be used for storing data
durably over time. This recommendation will change when the
specification is complete._ 

## Installation

```
$ composer require honzabrecka/transit-php
```

## Usage

```php
use transit\JSONReader;
use transit\JSONWriter;
use transit\Transit;
use transit\Map;

$transit = new Transit(new JSONReader(), new JSONWriter());
$transit->read('["^ ","foo","bar"]');
$transit->write([new Map(['foo', ['bar', true, 1.25]])]);
```

## Default Type Mapping

|Transit type|Write accepts|Read returns|
|------------|-------------|------------|
|null|null|null|
|string|string|string|
|boolean|bool|bool|
|integer|int|int|
|decimal|float|float|
|bytes|transit\Bytes|transit\Bytes|
|keyword|transit\Keyword|transit\Keyword|
|symbol|transit\Symbol|transit\Symbol|
|time|DateTime|DateTime|
|array|array|array|
|map|transit\Map|transit\Map|
|cmap|transit\Map|transit\Map|
|set|transit\Set|transit\Set|
|list|SplDoublyLinkedList|SplDoublyLinkedList|
|uri|transit\URI|transit\URI|
|uuid|transit\UUID|transit\UUID|
|char|transit\Char|transit\Char|