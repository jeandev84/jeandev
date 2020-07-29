<?php

$router = new Router();



/*
$options = [
    'prefix' => '/admin',
    'namespace' => 'Admin\\'
];

$router->group($options, function ($router) {

    $router->map('GET', '/', 'HomeController@index', 'home');

    $router->map('GET', '/about', function () {
        return 'About';
    }, 'about');



    $router->map('GET', '/contact', function () {
        return 'Contact';
    });

});

*/

$router->map('GET', '/:any', 'HomeController@index', 'home');

dump($router->getNamedRoutes());
dump($router->getRoutes());


$route = $router->match($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

dump($route);