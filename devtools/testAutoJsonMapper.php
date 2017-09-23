<?php

namespace Foo;

set_time_limit(0);
date_default_timezone_set('UTC');

require __DIR__.'/../vendor/autoload.php';

use InstagramAPI\AutoJsonMapper;

// TODO: This file was written extremely sloppily just to get some tests during
// development of the AutoJsonMapper... We should rewrite everything as a proper
// PHPUnit test... if someone has the energy...

$json = <<<EOF
{
  "just_a_string":"foo",
  "self_object":{
    "just_a_string":"foo2"
  },
  "camelCaseProp":1234,
  "string_array":[
    "123",
    "b",
    "c"
  ],
  "self_array":[
    {
      "just_a_string":"foo2"
    },
    {
      "just_a_string":"foo2"
    },
    {
      "just_a_string":"foo2"
    }
  ]
}
EOF;

class Test extends AutoJsonMapper
{
    protected static $_jsonProperties = [
        'just_a_string' => ['string', false],
        'camelCaseProp' => ['int', false],
        'self_object'   => ['\foo\Test', false],
        'string_array'  => ['string', true],
        'self_array'    => ['\foo\Test', true],
    ];
}

$data = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);

$x = new Test($data, true);
var_dump($x);
var_dump($x->getCamelCaseProp());
var_dump($x->getJustAString());
var_dump($x->isJustAString());
var_dump($x->getJustAString());
var_dump($x);
var_dump($x->getSelfObject());
var_dump($x->getSelfObject()->getJustAString());
var_dump($x->getStringArray());
var_dump($x->getSelfArray());
class SubClassOfTest extends Test
{
}
// class SubClassOfTest {} // Tests the rejection of non-instances of required class.
$foo = new SubClassOfTest();
$x->setSelfObject($foo);
var_dump($x->getSelfObject());
var_dump($x->getSelfObject()->getJustAString());
$foo = new Test(['just_a_string' => 'example']);
$x->setSelfObject($foo);
var_dump($x->getSelfObject());
var_dump($x->getSelfObject()->getJustAString());
$x->printJson();
var_dump($x->just_a_string);
var_dump(isset($x->just_a_string));
unset($x->just_a_string);
var_dump($x->just_a_string);
var_dump(isset($x->just_a_string));
unset($x->self_array);
unset($x->camelCaseProp);
$x->printJson();

var_dump('---------------------');

class TestUndefinedProps extends AutoJsonMapper
{
    protected static $_jsonProperties = [
        'self'      => ['\foo\TestUndefinedProps', false],
        'selfArray' => ['\foo\TestUndefinedProps', true],
        'property'  => ['string', false],
    ];
}

$json = <<<EOF
{
"self":{
  "yet_another_missing":"000"
},
"selfArray":[
  {
    "array_missing":"111"
  },
  {
    "array_missing":"222",
    "another_array_missing":"333"
  }
],
"property":"123",
"missing_property":"456",
"another_missing":"789"
}
EOF;

$data = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);

try {
    $x = new TestUndefinedProps($data, true);
} catch (\Exception $e) {
    printf("TestUndefinedProps Exception: %s\n", $e->getMessage());
}

var_dump('---------------------');

class TestBadSubClass extends AutoJsonMapper
{
    protected static $_jsonProperties = [
        'bad_subclass'      => ['\foo\BadSubClass', false],
    ];
}
class BadSubClass
{
} // Not instance of AutoJsonMapper

$json = <<<EOF
{
  "bad_subclass":{}
}
EOF;

$data = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);

try {
    $x = new TestBadSubClass($data, true);
    $x->getBadSubclass();
} catch (\Exception $e) {
    printf("TestBadSubClass Exception: %s\n", $e->getMessage());
}

var_dump('---------------------');

class TestMissingClass extends AutoJsonMapper
{
    protected static $_jsonProperties = [
        'a_missing_class'      => ['\foo\Missing', false],
    ];
}

$json = <<<EOF
{
  "a_missing_class":{}
}
EOF;

$data = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);

try {
    $x = new TestMissingClass($data, true);
} catch (\Exception $e) {
    printf("TestMissingClass Exception: %s\n", $e->getMessage());
}

var_dump('---------------------');

class TestNullValue extends AutoJsonMapper
{
    protected static $_jsonProperties = [
        'this_is_null'      => ['\foo\TestNullValue', false],
    ];
}

$json = <<<EOF
{
  "this_is_null":null
}
EOF;

$data = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);

try {
    $x = new TestNullValue($data, true);
    var_dump($x->getThisIsNull());
} catch (\Exception $e) {
    printf("TestNullValue Exception: %s\n", $e->getMessage());
}

var_dump('---------------------');

class TestNoCastValue extends AutoJsonMapper
{
    protected static $_jsonProperties = [
        'no_cast1' => ['', false],
        'no_cast2' => ['', false],
        'no_cast3' => ['', false],
    ];
}

$json = <<<EOF
{
  "no_cast1":3.14,
  "no_cast2":1234,
  "no_cast3":true
}
EOF;

$data = json_decode($json, true, 512, JSON_BIGINT_AS_STRING);

try {
    $x = new TestNoCastValue($data, true);
    var_dump($x->getNoCast1());
    var_dump($x->getNoCast2());
    var_dump($x->getNoCast3());
    $x->setNoCast1('should succeed without type-forcing');
    var_dump($x->getNoCast1());
} catch (\Exception $e) {
    printf("TestNoCastValue Exception: %s\n", $e->getMessage());
}
