<?php

use Jan\Component\Http\Request;
use Jan\Component\Http\Response;
use Jan\Component\Routing\Route;
use Jan\Component\Routing\Router;

require_once __DIR__.'/../vendor/autoload.php';


$container = new \Jan\Component\DI\Container();

$container->bind('single');
$container->bind('something', 'do somethings');
$container->bind('foo', 'Foo');
$container->bind('test', function () {
    return 'Test';
});

dump($container->get('something'));
dump($container->get('test'));

/*
$container->instance(\App\Person::class, new \App\Person());
dump($container->has(App\Person::class));
*/

dd($container);