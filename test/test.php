<?php

// TODO set
// TODO list
// TODO cmap
// TODO uri, uuid, ...

require __DIR__ . '/../vendor/autoload.php';

use Tester\Assert;
use transit\JSONReader;
use transit\JSONWriter;
use transit\Transit;
use transit\handlers\ExtensionHandler;
use transit\types\Keyword;
use transit\types\Symbol;

class Point {

    public $x;

    public $y;

    public function __construct($x, $y) {
        $this->x = $x;
        $this->y = $y;
    }

}

class Circle {

    public $a;

    public $b;

    public function __construct($a, $b) {
        $this->a = $a;
        $this->b = $b;
    }

}

class PointHandler implements ExtensionHandler {

    public function tag() {
        return 'point';
    }

    public function type() {
        return Point::class;
    }

    public function representation($obj) {
        return [$obj->x, $obj->y];
    }

    public function resolve($obj) {
        return new Point($obj[0], $obj[1]);
    }

}

class CircleHandler implements ExtensionHandler {

    public function tag() {
        return 'circle';
    }

    public function type() {
        return Circle::class;
    }

    public function representation($obj) {
        return [$obj->a, $obj->b];
    }

    public function resolve($obj) {
        return new Circle($obj[0], $obj[1]);
    }

}

class UnregisteredExtension {}

function t($verbose) {
  $t = new Transit(new JSONReader($verbose), new JSONWriter($verbose));
  $t->registerHandler(new PointHandler());
  $t->registerHandler(new CircleHandler());
  return $t;
}

function w($input) {
  return t(false)->write($input);
}

function r($input) {
  return t(false)->read($input);
}

function wv($input) {
  return t(true)->write($input);
}

function rv($input) {
  return t(true)->read($input);
}

//-------------------------
// write

// simple write
Assert::equal('["~#\'","foo"]', w('foo'));
Assert::equal('["~#\'",0]', w(0));
Assert::equal('["~#\'",1]', w(1));
Assert::equal('["~#\'",2]', w(2));
Assert::equal('["~#\'",2.5]', w(2.5));
Assert::equal('["~#\'",null]', w(null));
Assert::equal('["~#\'",true]', w(true));
Assert::equal('["~#\'",false]', w(false));
Assert::equal('["~#\'","~zNaN"]', w(NAN));
Assert::equal('["~#\'","~zINF"]', w(INF));
Assert::equal('["~#\'","~z-INF"]', w(-INF));

// vector simple write
Assert::equal('[]', w([]));
Assert::equal('["foo"]', w(['foo']));
Assert::equal('[0]', w([0]));
Assert::equal('[1]', w([1]));
Assert::equal('[2]', w([2]));
Assert::equal('[2.5]', w([2.5]));
Assert::equal('[null]', w([null]));
Assert::equal('[true]', w([true]));
Assert::equal('[false]', w([false]));
Assert::equal('[1,2,3]', w([1, 2, 3]));
Assert::equal('["","a","ab","abc"]', w(['', 'a', 'ab', 'abc']));
Assert::equal('["~zNaN"]', w([NAN]));
Assert::equal('["~zINF"]', w([INF]));
Assert::equal('["~z-INF"]', w([-INF]));

Assert::equal('["~~foo"]', w(['~foo']));
Assert::equal('["~^foo"]', w(['^foo']));

// map simple write
Assert::equal('["^ "]', w((object)[]));
Assert::equal('["^ ","foo","bar"]', w((object)['foo' => 'bar']));
Assert::equal('["^ ","~i6","six"]', w((object)[6 => 'six']));
//Assert::equal('["^ ","~d1.25","x"]', w((object)[1.25 => 'x']));// TODO currently unsupported
//Assert::equal('["^ ","~?t","x"]', w((object)[true => 'x']));// TODO currently unsupported
//Assert::equal('["^ ","~?f","x"]', w((object)[false => 'x']));// TODO currently unsupported
//Assert::equal('["^ ","~_","x"]', w((object)[null => 'x']));// TODO currently unsupported
//Assert::equal('["^ ","~zNaN","x"]', w((object)[NAN => 'x']));// TODO currently unsupported
//Assert::equal('["^ ","~zINF","x"]', w((object)[INF => 'x']));// TODO currently unsupported
//Assert::equal('["^ ","~z-INF","x"]', w((object)[-INF => 'x']));// TODO currently unsupported
//Assert::equal('["^ ","~:a","b"]', w((object)[new Symbol('a') => 'b'])); // TODO symbol
//Assert::equal('["^ ","~$a","b"]', w((object)[new Keyword('a') => 'b']));// TODO keyword

// mixed/nested write
Assert::equal('[[1,2,3],[[4]]]', w([[1, 2, 3], [[4]]]));
Assert::equal('[["^ ","foo",["bar",true,1.25]]]', w([(object)['foo' => ['bar', true, 1.25]]]));

// custom write
Assert::equal('["~#point",[10,20]]', w(new Point(10, 20)));
Assert::equal('["~#circle",[["~#point",[10,20]],5]]', w(new Circle(new Point(10, 20), 5)));

Assert::exception(function() {
  w(new UnregisteredExtension());
}, 'transit\TransitException');

//-------------------------
// read

Assert::exception(function() {
  r('[');
}, 'transit\TransitException');

Assert::exception(function() {
  r('{}');
}, 'transit\TransitException');

Assert::exception(function() {
  r('["~#\'"]');
}, 'transit\TransitException');

Assert::exception(function() {
  r('["^ ",1]');
}, 'transit\TransitException');

Assert::exception(function() {
  r('["~#\'",1,2]');
}, 'transit\TransitException');

// BC read
Assert::equal('foo', r('"foo"'));
Assert::equal(0, r('0'));
Assert::equal(1, r('1'));
Assert::equal(2, r('2'));
Assert::equal(2.5, r('2.5'));
Assert::equal(null, r('null'));
Assert::equal(true, r('true'));
Assert::equal(false, r('false'));

// simple read
Assert::equal('foo', r('["~#\'","foo"]'));
Assert::equal(0, r('["~#\'",0]'));
Assert::equal(1, r('["~#\'",1]'));
Assert::equal(2, r('["~#\'",2]'));
Assert::equal(2.5, r('["~#\'",2.5]'));
Assert::equal(null, r('["~#\'",null]'));
Assert::equal(true, r('["~#\'",true]'));
Assert::equal(false, r('["~#\'",false]'));

Assert::equal(['~foo'], r('["~~foo"]'));
Assert::equal(['^foo'], r('["~^foo"]'));

// vector simple read
Assert::equal([], r('[]'));
Assert::equal(['foo'], r('["foo"]'));
Assert::equal([0], r('[0]'));
Assert::equal([1], r('[1]'));
Assert::equal([2], r('[2]'));
Assert::equal([2.5], r('[2.5]'));
Assert::equal([null], r('[null]'));
Assert::equal([true], r('[true]'));
Assert::equal([false], r('[false]'));
Assert::equal([1, 2, 3], r('[1,2,3]'));
Assert::equal(['', 'a', 'ab', 'abc'], r('["","a","ab","abc"]'));
Assert::nan(r('["~zNaN"]')[0]);
Assert::equal([INF], r('["~zINF"]'));
Assert::equal([-INF],r('["~z-INF"]'));

// map simple read
Assert::equal((object)[], r('["^ "]'));
Assert::equal((object)['foo' => 'bar'], r('["^ ","foo","bar"]'));
Assert::equal((object)[6 => 'six'], r('["^ ","~i6","six"]'));

// mixed/nested read
Assert::equal([[1, 2, 3], [[4]]], r('[[1,2,3],[[4]]]'));
Assert::equal([(object)['foo' => ['bar', true, 1.25]]], r('[["^ ","foo",["bar",true,1.25]]]'));

// custom read
Assert::equal(new Point(10, 20), r('["~#point",[10,20]]'));
Assert::equal(new Circle(new Point(10, 20), 5), r('["~#circle",[["~#point",[10,20]],5]]'));

// caching
Assert::equal('[["^ ","aaaa","b"],["^ ","^0","b"],["^ ","^0","b"]]', w([(object)['aaaa' => 'b'], (object)['aaaa' => 'b'], (object)['aaaa' => 'b']]));
Assert::equal('[[["^ ","aaaa","b"]],["^ ","^0","b"]]', w([[(object)['aaaa' => 'b']], (object)['aaaa' => 'b']]));
Assert::equal('[["^ ","aaaa","b"],[["^ ","^0","b"]]]', w([(object)['aaaa' => 'b'], [(object)['aaaa' => 'b']]]));

Assert::equal([(object)['aaaa' => 'b'], (object)['aaaa' => 'b'], (object)['aaaa' => 'b']], r('[["^ ","aaaa","b"],["^ ","^0","b"],["^ ","^0","b"]]'));
Assert::equal([[(object)['aaaa' => 'b']], (object)['aaaa' => 'b']], r('[[["^ ","aaaa","b"]],["^ ","^0","b"]]'));
Assert::equal([(object)['aaaa' => 'b'], [(object)['aaaa' => 'b']]], r('[["^ ","aaaa","b"],[["^ ","^0","b"]]]'));

// keywords
Assert::equal('["~:x"]', w([new Keyword('x')]));
Assert::equal((string)new Keyword('x'), (string)r('["~:x"]')[0]);

// keywords
Assert::equal('["~$x"]', w([new Symbol('x')]));
Assert::equal((string)new Symbol('x'), (string)r('["~$x"]')[0]);

//////////
// verbose
//////////

//-------------------------
// write

// simple write
Assert::equal('{"~#\'":"foo"}', wv('foo'));
Assert::equal('{"~#\'":0}', wv(0));
Assert::equal('{"~#\'":1}', wv(1));
Assert::equal('{"~#\'":2}', wv(2));
Assert::equal('{"~#\'":2.5}', wv(2.5));
Assert::equal('{"~#\'":null}', wv(null));
Assert::equal('{"~#\'":true}', wv(true));
Assert::equal('{"~#\'":false}', wv(false));
Assert::equal('{"~#\'":"~zNaN"}', wv(NAN));
Assert::equal('{"~#\'":"~zINF"}', wv(INF));
Assert::equal('{"~#\'":"~z-INF"}', wv(-INF));

// vector simple write
Assert::equal('[]', wv([]));
Assert::equal('["foo"]', wv(['foo']));
Assert::equal('[0]', wv([0]));
Assert::equal('[1]', wv([1]));
Assert::equal('[2]', wv([2]));
Assert::equal('[2.5]', wv([2.5]));
Assert::equal('[null]', wv([null]));
Assert::equal('[true]', wv([true]));
Assert::equal('[false]', wv([false]));
Assert::equal('[1,2,3]', wv([1, 2, 3]));
Assert::equal('["","a","ab","abc"]', wv(['', 'a', 'ab', 'abc']));
Assert::equal('["~zNaN"]', wv([NAN]));
Assert::equal('["~zINF"]', wv([INF]));
Assert::equal('["~z-INF"]', wv([-INF]));

Assert::equal('["~~foo"]', w(['~foo']));
Assert::equal('["~^foo"]', w(['^foo']));

// map simple write
Assert::equal('{}', wv((object)[]));
Assert::equal('{"foo":"bar"}', wv((object)['foo' => 'bar']));
Assert::equal('{"~i6":"six"}', wv((object)[6 => 'six']));

// mixed/nested write
Assert::equal('[[1,2,3],[[4]]]', wv([[1, 2, 3], [[4]]]));
Assert::equal('[{"foo":["bar",true,1.25]}]', wv([(object)['foo' => ['bar', true, 1.25]]]));

// custom write
Assert::equal('{"~#point":[10,20]}', wv(new Point(10, 20)));
Assert::equal('{"~#circle":[{"~#point":[10,20]},5]}', wv(new Circle(new Point(10, 20), 5)));

Assert::exception(function() {
  wv(new UnregisteredExtension());
}, 'transit\TransitException');

//-------------------------
// read

Assert::exception(function() {
  rv('[');
}, 'transit\TransitException');

Assert::exception(function() {
  rv('{"~#\'"}');
}, 'transit\TransitException');

Assert::exception(function() {
  rv('{1}');
}, 'transit\TransitException');

Assert::exception(function() {
  rv('{"~#\'":"a","b":"c"}');
}, 'transit\TransitException');

// BC read
Assert::equal('foo', rv('"foo"'));
Assert::equal(0, rv('0'));
Assert::equal(1, rv('1'));
Assert::equal(2, rv('2'));
Assert::equal(2.5, rv('2.5'));
Assert::equal(null, rv('null'));
Assert::equal(true, rv('true'));
Assert::equal(false, rv('false'));

// simple read
Assert::equal('foo', rv('{"~#\'":"foo"}'));
Assert::equal(0, rv('{"~#\'":0}'));
Assert::equal(1, rv('{"~#\'":1}'));
Assert::equal(2, rv('{"~#\'":2}'));
Assert::equal(2.5, rv('{"~#\'":2.5}'));
Assert::equal(null, rv('{"~#\'":null}'));
Assert::equal(true, rv('{"~#\'":true}'));
Assert::equal(false, rv('{"~#\'":false}'));

Assert::equal(['~foo'], r('["~~foo"]'));
Assert::equal(['^foo'], r('["~^foo"]'));

// vector simple read
Assert::equal([], rv('[]'));
Assert::equal(['foo'], rv('["foo"]'));
Assert::equal([0], rv('[0]'));
Assert::equal([1], rv('[1]'));
Assert::equal([2], rv('[2]'));
Assert::equal([2.5], rv('[2.5]'));
Assert::equal([null], rv('[null]'));
Assert::equal([true], rv('[true]'));
Assert::equal([false], rv('[false]'));
Assert::equal([1, 2, 3], rv('[1,2,3]'));
Assert::equal(['', 'a', 'ab', 'abc'], rv('["","a","ab","abc"]'));
Assert::nan(rv('["~zNaN"]')[0]);
Assert::equal([INF], rv('["~zINF"]'));
Assert::equal([-INF],rv('["~z-INF"]'));

// map simple read
Assert::equal((object)[], rv('{}'));
Assert::equal((object)['foo' => 'bar'], rv('{"foo":"bar"}'));
Assert::equal((object)[6 => 'six'], rv('{"~i6":"six"}'));

// mixed/nested read
Assert::equal([[1, 2, 3], [[4]]], rv('[[1,2,3],[[4]]]'));
Assert::equal([(object)['foo' => ['bar', true, 1.25]]], rv('[{"foo":["bar",true,1.25]}]'));

// custom read
Assert::equal(new Point(10, 20), rv('{"~#point":[10,20]}'));
Assert::equal(new Circle(new Point(10, 20), 5), rv('{"~#circle":[{"~#point":[10,20]},5]}'));

// caching
Assert::equal('[{"aaaa":"b"},{"^0":"b"},{"^0":"b"}]', wv([(object)['aaaa' => 'b'], (object)['aaaa' => 'b'], (object)['aaaa' => 'b']]));
Assert::equal('[[{"aaaa":"b"}],{"^0":"b"}]', wv([[(object)['aaaa' => 'b']], (object)['aaaa' => 'b']]));
Assert::equal('[{"aaaa":"b"},[{"^0":"b"}]]', wv([(object)['aaaa' => 'b'], [(object)['aaaa' => 'b']]]));

Assert::equal([(object)['aaaa' => 'b'], (object)['aaaa' => 'b'], (object)['aaaa' => 'b']], rv('[{"aaaa":"b"},{"^0":"b"},{"^0":"b"}]'));
Assert::equal([[(object)['aaaa' => 'b']], (object)['aaaa' => 'b']], rv('[[{"aaaa":"b"}],{"^0":"b"}]'));
Assert::equal([(object)['aaaa' => 'b'], [(object)['aaaa' => 'b']]], rv('[{"aaaa":"b"},[{"^0":"b"}]]'));

// keywords
Assert::equal('["~:x"]', wv([new Keyword('x')]));
Assert::equal((string)new Keyword('x'), (string)rv('["~:x"]')[0]);

// keywords
Assert::equal('["~$x"]', w([new Symbol('x')]));
Assert::equal((string)new Symbol('x'), (string)r('["~$x"]')[0]);
