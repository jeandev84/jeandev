<?php

use Jan\Component\Http\Request;
use Jan\Component\Http\Response;
use Jan\Component\Routing\Route;
use Jan\Component\Routing\Router;

require_once __DIR__.'/../vendor/autoload.php';


$container = new \Jan\Component\DI\Container();

// BINDINGS
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


$container->instance(\App\Person::class, new \App\Person());
$container->bind(\App\Person::class, \App\Person::class);

$container->instance(\App\Person::class, new \App\Person());
$container->singleton(\App\PersonInterface::class, \App\Person::class);
dump($container->get(App\PersonInterface::class));
dump($container->get(App\PersonInterface::class));
dump($container->get(App\PersonInterface::class));


dump($container->make(\App\Foo::class));
dump($container->make(\App\Foo::class));
dump($container->make(\App\Foo::class));
dump($container->make(\App\Foo::class));
*/

$container->bind('foo', function () {

    return new \App\Foo(new \App\Bar());
});

dump($container->get('foo'));


$container->instance(\App\Person::class, new \App\Person());
// $container->bind(\App\Person::class, \App\Person::class);

dump($container->get(\App\Person::class));

dd($container);