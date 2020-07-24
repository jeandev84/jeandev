<?php


use Jan\Component\Routing\Route;
use Jan\Component\Routing\Router;

require_once __DIR__.'/../vendor/autoload.php';


# USABLE 1
/*

$router = new Router();

$routes = [
    [
        'methods' => ['GET'],
        'path' => '/',
        'target' => function () {

        },
        'name' => 'home'
    ],
    [
        'methods' => ['POST'],
        'path' => '/contact',
        'target' => function () {

        },
        'name' => 'contact'
    ],
];


$router->setRoutes($routes);

*/


# USABLE 2

$router = new Router();

$router->map('GET', '/', 'HomeController@index')
->name('home')
->middleware([
  \App\Middlewares\Authenticated::class,
  \App\Middlewares\CsrfToken::class
]);

$router->map('GET', '/about', 'HomeController@about', 'about')
->middleware([
    \App\Foo::class
]);


$router->map('GET|POST', '/contact', 'HomeController@contact', 'contact');


dump($router->getRoutes());


$route = $router->match($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

dd($route);
