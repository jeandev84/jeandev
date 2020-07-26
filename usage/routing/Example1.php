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
            return 'Hello';
        },
        'name' => 'home'
    ],
    [
        'methods' => ['POST'],
        'path' => '/contact',
        'target' => function () {
            return [
               'name'  => 'Жан-Клод',
               'email' => 'jeanyao@ymail.com'
            ];
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

$router->map('GET', '/post/{slug}/{id}', 'HomeController@contact', 'post.show')
    ->where([
        'slug' => '(\w+)',
        'id'   => '(\d+)'
    ]);


dump($router->getNamedRoutes());
dump($router->getRoutes());


$route = $router->match($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

dd($route);
