<?php

use Jan\Component\Http\Request;
use Jan\Component\Http\Response;
use Jan\Component\Routing\Route;
use Jan\Component\Routing\Router;

require_once __DIR__.'/../vendor/autoload.php';


$container = new \Jan\Component\DI\Container();

$container->instance(\Jan\Component\DI\Contracts\ContainerInterface::class, $container);


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

/*
$container->bind('foo', function () {

    return new \App\Foo(new \App\Bar());
});

dump($container->get('foo'));
*/


$container->instance(\App\Person::class, new \App\Person());
// $container->bind(\App\Person::class, \App\Person::class);

// dump($container->get(\App\Person::class));

// $container->instance(\Jan\Component\DI\Contracts\ContainerInterface::class, new \Jan\Component\DI\Container());
$container->call(\App\Controllers\HomeController::class, ['slug' => 'article-1', 'id' => 1], 'index');

/*
$container->call(function (Request $request, Response $response) {

    dump($request, $response);
    echo 'Привет!';
});
*/

$router = new Router();

$router->map('GET', '/', function (Request $request, Response $response) {
    echo $request->getMethod() . '<br>';
    echo 'Привет! Мир!';
},'home');

$router->map('GET', '/foo/{id}', function (Request $request, Response $response) {
    echo $request->getUri() . '<br>';
    echo 'Foo!';
},'foo');


$route = $router->match($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

// dump($route->getTarget());

if(! $route)
{
    exit('404 Page not found!');
}

$response = $container->call($route->getTarget(), $route->getMatches());

dd($container);