<?php


use Jan\Component\Routing\Route;
use Jan\Component\Routing\Router;

require_once __DIR__.'/../vendor/autoload.php';


$container = new \Jan\Component\DI\Container();

/*
$container->bind('name', 'Жан-Клод');
$container->bind('foo', function () {
    return new \App\Foo();
});

echo $container->get('name');
dump($container->get('foo'));
*/

/*
$container->bind(\App\Foo::class, function () {
    return new \App\Foo();
});
*/

$container->bind('something', 'Hello! Friend');
$container->singleton(\App\Foo::class, App\Bar::class);

dump($container->get(\App\Foo::class));


echo $container->get('something');

/*
dump($container->get(\App\Foo::class));
dump($container->get(\App\Foo::class));
dump($container->get(\App\Foo::class));


dump($container->make(\App\Foo::class));
dump($container->make(\App\Foo::class));
dump($container->make(\App\Foo::class));

$container->make(\App\Controllers\HomeController::class);
*/

dd($container);