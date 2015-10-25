<?php

require __DIR__ . '/../vendor/autoload.php';

use Tester\Assert;
use transit\JSONReader;
use transit\JSONWriter;
use transit\Transit;
use transit\handlers\Handler;
use transit\Keyword;
use transit\Symbol;
use transit\Map;
use transit\Set;
use transit\Bytes;
use transit\URI;
use transit\UUID;
use transit\Char;

//-------------------------
// structs

// map
Assert::exception(function() {
  new Map(['keyOnly']);
}, 'transit\TransitException');

$m = new Map([true, 'a']);
Assert::equal('a', $m[true]);
Assert::true(isset($m[true]));
Assert::equal([true, 'a'], $m->toArray());

$m[true] = 'b';
Assert::equal('b', $m[true]);
Assert::equal([true, 'b'], $m->toArray());

unset($m[true]);
Assert::false(isset($m[true]));
Assert::equal([], $m->toArray());

Assert::false(isset($m['whatever']));

$m[1] = 'x';
$m[1.0] = 'y';
$m[new Keyword('abc')] = 'z';
Assert::equal([1, 'x', 1.0, 'y', new Keyword('abc'), 'z'], $m->toArray());
Assert::equal('x', $m[1]);
Assert::equal('y', $m[1.0]);
Assert::equal('z', $m[new Keyword('abc')]);
Assert::true(isset($m[1]));
Assert::true(isset($m[1.0]));
Assert::true(isset($m[new Keyword('abc')]));
Assert::false(isset($m['whatever']));

unset($m[1.000]);
Assert::equal([1, 'x', new Keyword('abc'), 'z'], $m->toArray());
Assert::equal('x', $m[1]);
Assert::equal('z', $m[new Keyword('abc')]);
Assert::true(isset($m[1]));
Assert::true(isset($m[new Keyword('abc')]));

// set
$s = new Set(['a']);

Assert::exception(function() use ($s) {
  $s->add('a');
}, 'transit\TransitException');

Assert::true($s->contains('a'));

$s->add(new Keyword('abc'));
$s->add('b');
$s->add(true);
Assert::equal(['a', new Keyword('abc'), 'b', true], $s->toArray());

$s->remove(new Keyword('abc'));
Assert::equal(['a', 'b', true], $s->toArray());
Assert::false($s->contains(new Keyword('abc')));
Assert::true($s->contains('a'));
Assert::true($s->contains('b'));
Assert::true($s->contains(true));

//-------------------------

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

class PointHandler implements Handler {

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

class CircleHandler implements Handler {

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

function t() {
  $t = new Transit(new JSONReader(), new JSONWriter());
  $t->registerHandler(new PointHandler());
  $t->registerHandler(new CircleHandler());
  return $t;
}

function w($input) {
  return t()->write($input);
}

function r($input) {
  return t()->read($input);
}

//-------------------------
// write

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
Assert::equal('["~#\'","~:a"]', w(new Keyword('a')));
Assert::equal('["~#\'","~$a"]', w(new Symbol('a')));

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
Assert::equal('["~:a"]', w([new Keyword('a')]));
Assert::equal('["~$a"]', w([new Symbol('a')]));

Assert::equal('["~~foo"]', w(['~foo']));
Assert::equal('["~^foo"]', w(['^foo']));

Assert::equal('["^ "]', w(new Map([])));
Assert::equal('["^ ","foo","bar"]', w(new Map(['foo', 'bar'])));
Assert::equal('["^ ","~i6","six"]', w(new Map([6, 'six'])));
Assert::equal('["^ ","~d1.25","x"]', w(new Map([1.25, 'x'])));
Assert::equal('["^ ","~?t","x"]', w(new Map([true, 'x'])));
Assert::equal('["^ ","~?f","x"]', w(new Map([false, 'x'])));
Assert::equal('["^ ","~_","x"]', w(new Map([null, 'x'])));
Assert::equal('["^ ","~zNaN","x"]', w(new Map([NAN, 'x'])));
Assert::equal('["^ ","~zINF","x"]', w(new Map([INF, 'x'])));
Assert::equal('["^ ","~z-INF","x"]', w(new Map([-INF, 'x'])));
Assert::equal('["^ ","~$a","b"]', w(new Map([new Symbol('a'), 'b'])));
Assert::equal('["^ ","~:a","b"]', w(new Map([new Keyword('a'), 'b'])));
Assert::equal('["~#cmap",[["a"],"b"]]', w(new Map([['a'], 'b'])));
Assert::equal('["~#cmap",[["~#set",["a"]],"b"]]', w(new Map([new Set(['a']), 'b'])));
Assert::equal('["~#cmap",[["^ ","foo","bar"],"b"]]', w(new Map([new Map(['foo', 'bar']), 'b'])));

Assert::equal('[[1,2,3],[[4]]]', w([[1, 2, 3], [[4]]]));
Assert::equal('[["^ ","foo",["bar",true,1.25]]]', w([new Map(['foo', ['bar', true, 1.25]])]));

Assert::equal('["~#point",[10,20]]', w(new Point(10, 20)));
Assert::equal('["~#circle",[["~#point",[10,20]],5]]', w(new Circle(new Point(10, 20), 5)));

Assert::exception(function() {
  w(new UnregisteredExtension());
}, 'transit\TransitException');

Assert::exception(function() {
  w((object)['a' => 'b']);
}, 'transit\TransitException');

Assert::equal('["value"]', w(['key' => 'value']));

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

Assert::equal('foo', r('"foo"'));
Assert::equal(0, r('0'));
Assert::equal(1, r('1'));
Assert::equal(2, r('2'));
Assert::equal(2.5, r('2.5'));
Assert::equal(null, r('null'));
Assert::equal(true, r('true'));
Assert::equal(false, r('false'));
Assert::nan(r('"~zNaN"'));
Assert::equal(INF, r('"~zINF"'));
Assert::equal(-INF, r('"~z-INF"'));
Assert::equal(new Keyword('a'), r('"~:a"'));
Assert::equal(new Symbol('a'), r('"~$a"'));

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

Assert::equal(new Map([]), r('["^ "]'));
Assert::equal(new Map(['foo', 'bar']), r('["^ ","foo","bar"]'));
Assert::equal(new Map([6, 'six']), r('["^ ","~i6","six"]'));
Assert::equal(new Map([1.25, 'x']), r('["^ ","~d1.25","x"]'));
Assert::equal(new Map([true, 'x']), r('["^ ","~?t","x"]'));
Assert::equal(new Map([false, 'x']), r('["^ ","~?f","x"]'));
Assert::equal(new Map([null, 'x']), r('["^ ","~_","x"]'));
//Assert::equal(new Map([NAN, 'x']), r('["^ ","~zNaN","x"]'));
Assert::equal(new Map([INF, 'x']), r('["^ ","~zINF","x"]'));
Assert::equal(new Map([-INF, 'x']), r('["^ ","~z-INF","x"]'));
Assert::equal(new Map([new Symbol('a'), 'b']), r('["^ ","~$a","b"]'));
Assert::equal(new Map([new Keyword('a'), 'b']), r('["^ ","~:a","b"]'));
Assert::equal(new Map([['a'], 'b']), r('["~#cmap",[["a"],"b"]]'));
Assert::equal(new Map([new Set(['a']), 'b']), r('["~#cmap",[["~#set",["a"]],"b"]]'));
Assert::equal(new Map([new Map(['foo', 'bar']), 'b']), r('["~#cmap",[["^ ","foo","bar"],"b"]]'));

Assert::equal([[1, 2, 3], [[4]]], r('[[1,2,3],[[4]]]'));
Assert::equal([new Map(['foo', ['bar', true, 1.25]])], r('[["^ ","foo",["bar",true,1.25]]]'));

Assert::equal(new Point(10, 20), r('["~#point",[10,20]]'));
Assert::equal(new Circle(new Point(10, 20), 5), r('["~#circle",[["~#point",[10,20]],5]]'));

//-------------------------
// caching

Assert::equal('[["^ ","aaaa","b"],["^ ","^0","b"],["^ ","^0","b"]]', w([new Map(['aaaa', 'b']), new Map(['aaaa', 'b']), new Map(['aaaa', 'b'])]));
Assert::equal('[[["^ ","aaaa","b"]],["^ ","^0","b"]]', w([[new Map(['aaaa', 'b'])], new Map(['aaaa', 'b'])]));
Assert::equal('[["^ ","aaaa","b"],[["^ ","^0","b"]]]', w([new Map(['aaaa', 'b']), [new Map(['aaaa', 'b'])]]));

Assert::equal([new Map(['aaaa', 'b']), new Map(['aaaa', 'b']), new Map(['aaaa', 'b'])], r('[["^ ","aaaa","b"],["^ ","^0","b"],["^ ","^0","b"]]'));
Assert::equal([[new Map(['aaaa', 'b'])], new Map(['aaaa', 'b'])], r('[[["^ ","aaaa","b"]],["^ ","^0","b"]]'));
Assert::equal([new Map(['aaaa', 'b']), [new Map(['aaaa', 'b'])]], r('[["^ ","aaaa","b"],[["^ ","^0","b"]]]'));

//-------------------------
// extensions

Assert::equal('["~m482196050"]', w([new DateTime('1985-04-12T23:20:50.52Z')]));
//Assert::equal([new DateTime('1985-04-12T23:20:50.52Z')], r('["~m482196050"]'));

Assert::equal('["~bYWJj"]', w([new Bytes('abc')]));
Assert::equal([new Bytes('abc')], r('["~bYWJj"]'));

Assert::equal('["~rhttp://php.net/"]', w([new URI('http://php.net/')]));
Assert::equal([new URI('http://php.net/')], r('["~rhttp://php.net/"]'));

Assert::equal('["~u531a379e-31bb-4ce1-8690-158dceb64be6"]', w([new UUID('531a379e-31bb-4ce1-8690-158dceb64be6')]));
Assert::equal([new UUID('531a379e-31bb-4ce1-8690-158dceb64be6')], r('["~u531a379e-31bb-4ce1-8690-158dceb64be6"]'));

Assert::equal('["~ca"]', w([new Char('a')]));
Assert::equal([new Char('a')], r('["~ca"]'));