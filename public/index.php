<?php

use Jan\Component\Http\Request;
use Jan\Component\Http\Response;
use Jan\Component\Routing\Route;
use Jan\Component\Routing\Router;

require_once __DIR__.'/../vendor/autoload.php';


$container = new \Jan\Component\DI\Container();

$container->bind('pipe', 'some pipe');

$container->instance(\Jan\Component\DI\Container::class, $container); /* recommended */

$container->singleton(\App\Foo::class, \App\Foo::class);

$container->get(\App\Foo::class);
$container->bind('something', 'Hello! Friend');
dump($container->get('something'));


dump($container->make(\Jan\Component\DI\Container::class));
dump($container->make(\Jan\Component\DI\Container::class));
dump($container->make(\Jan\Component\DI\Container::class));

dump($container->get(\Jan\Component\DI\Container::class));


echo 'C1';
//dump($c);
//dump($c);
//dd('End');
echo 'Container';
// dd($container->get(\Jan\Component\DI\Container::class));

dd('OK');
$container->bind('app', function (\Jan\Component\DI\Container  $container, Request $request, Response $response) {

    return $request->getMethod() . ', My app <br>';
});


echo $container->get('app');

/*
Testing Bindings / Singleton / Aliases
$container->bind('name', 'Жан-Клод');
$container->bind('foo', function () {
    return new \App\Foo();
});

echo $container->get('name');
dump($container->get('foo'));

$container->bind(\App\Foo::class, function () {
    return new \App\Foo();
});

$container->bind('something', 'Hello! Friend');
$container->singleton(\App\Foo::class, App\Bar::class);
dump($container->get(\App\Foo::class));
$container->alias('Route', \App\Foo::class);
dump($container->get('Route'));
echo $container->get('something');

dump($container->get(\App\Foo::class));
dump($container->get(\App\Foo::class));
dump($container->get(\App\Foo::class));


dump($container->make(\App\Foo::class));
dump($container->make(\App\Foo::class));
dump($container->make(\App\Foo::class));

$container->make(\App\Controllers\HomeController::class);
*/

/*
Testing Service provider
$container->bind(\App\Bar::class, new \App\Bar());
$container->setAlias('foo', \App\Foo::class);
*/

// $container->bind(\App\Bar::class, new \App\Bar());
// $container->singleton(\App\Foo::class, \App\Foo::class);

dump($container->get(\App\Foo::class));
/*
dd($container->get(\App\Foo::class));
$container->addServiceProvider(
    new \App\Providers\AppServiceProvider()
);

dump($container->get('foo'));


Testing call method
$person = new \App\Person();
$container->call($person, 'setName', ['Жан-Клод', 'jeanyao@ymail.com']);
dump($person->getName(), $person->getEmail());
$container->autowire(false);
*/

$container->singleton(\Jan\Component\DI\Contracts\ContainerInterface::class, function () {
    return new \Jan\Component\DI\Container();
});

/* $container->singleton(\App\Foo::class, \App\Foo::class); */
/* $container->call(\App\Controllers\HomeController::class, ['slug' => 'salut-les-amis', 'id' => 1], 'index'); */

$container->call(function () {
    echo 'Привет';
});


$container->call(\App\Controllers\HomeController::class, ['slug' => 'salut-les-amis', 'id' => 1], 'index');

$container->setCallback(\App\Controllers\HomeController::class,  ['slug' => 'salut-les-amis', 'id' => 1], 'index');


/*
$container->closure(function (\Jan\Component\DI\Container  $container, Request $request, Response $response) {

    echo $request->getMethod();
    echo 'Hello my web closure!';
});
*/

/*
$container->bootCallMethod(\App\Controllers\HomeController::class);
$container->call(\App\Controllers\HomeController::class, ['slug' => 'salut-les-amis', 'id' => 1], 'index');
*/

$container->calling();

dd($container);