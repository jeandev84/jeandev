<?php
use Jan\Component\Routing\Route;
use Jan\Component\Routing\Router;

require_once __DIR__.'/../vendor/autoload.php';


$container = new \Jan\Component\DI\Container();

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
$container->setAlias('Route', \App\Foo::class);
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
*/
$container->bind(\App\Bar::class, new \App\Bar());
// $container->setAlias('foo', \App\Foo::class);
$container->singleton(\App\Foo::class, \App\Foo::class);
/* dd($container->get(\App\Foo::class)); */

$container->addServiceProvider(
    new \App\Providers\AppServiceProvider()
);

// dump($container->get('foo'));


/*
Testing call method
$person = new \App\Person();
$container->call($person, 'setName', ['Жан-Клод', 'jeanyao@ymail.com']);
dump($person->getName(), $person->getEmail());
*/


//$container->autowire(false);
$container->singleton(\Jan\Component\DI\Contracts\ContainerInterface::class, function () {

    return new \Jan\Component\DI\Container();
});

$container->callAction(\App\Controllers\HomeController::class, 'index', ['slug' => 'salut-les-amis', 'id' => 1]);
//$container->boot(\App\Controllers\HomeController::class);
// $container->call(new \App\Controllers\HomeController(), 'index', []);


$container->calls();

dd($container);