<?php


use Jan\Component\Routing\Router;

require_once __DIR__.'/../vendor/autoload.php';


$router = new Router();

$options = [
    'prefix' => 'admin',
    'namespace' => 'Admin'
];

$router->group($options, function () use ($router){

    $router->map('GET', '/posts', 'PostController@index')
        ->name('admin.post.index');

    $router->map('GET', '/post/{id}', 'PostController@shpw')
        ->name('admin.post.show');


    $router->map('GET', '/post/new', 'PostController@new')
        ->name('admin.post.create');

    $router->map('GET|POST', '/post/{id}/edit', 'PostController@edit')
        ->name('admin.post.edit');


    $router->map('DELETE', '/post/{id}/delete', 'PostController@delete')
        ->name('admin.post.delete');

});

dump($router->getNamedRoutes());
dump($router->getRoutes());


$route = $router->match($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

dd($route);
