<?php


use Jan\Component\Routing\Route;
use Jan\Component\Routing\Router;

require_once __DIR__.'/../vendor/autoload.php';


$router = new Router();

/*
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

*/


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


/*
$router->map('GET', '/post/{slug}/{id}', 'HomeController@contact', 'post.show')
->where([
  'slug' => '(\w+)',
   'id'   => '(\d+)'
]);
*/


dump($router->getNamedRoutes());
dump($router->getRoutes());


$route = $router->match($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

dd($route);
