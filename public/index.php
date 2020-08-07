<?php

use Jan\Component\Http\Request;
use Jan\Component\Http\Response;
use Jan\Component\Routing\Router;

require_once __DIR__.'/../vendor/autoload.php';


$container = new \Jan\Component\DI\Container();

$router = new Router();

$router->map('GET', '/', function (Request $request, Response $response) {
    echo $request->getMethod() . '<br>';
    echo 'Привет! Мир!';
},'home');

$router->map('GET', '/foo/{id}', function (Request $request, Response $response) {
    echo $request->getUri() . '<br>';
    echo 'Foo!';
},'foo');


/*
$router->map('GET', '/foo/?{id}', function (Request $request, Response $response) {
    echo $request->getUri() . '<br>';
    echo 'Foo!';
},'foo');
*/


$route = $router->match($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

// dump($route->getTarget());

if(! $route)
{
    exit('404 Page not found!');
}

dump($route);

$response = $container->call($route->getTarget(), $route->getMatches());

dd($container);