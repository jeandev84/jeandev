<?php


use Jan\Component\Routing\Route;
use Jan\Component\Routing\Router;

require_once __DIR__.'/../vendor/autoload.php';


$router = new Router($_SERVER['HTTP_HOST']);


/*
$router->map('GET', '/', 'HomeController@index', 'home');
$router->map('GET', '/about', 'HomeController@about', 'about');
$router->map('GET|POST', '/contact', 'HomeController@contact', 'contact');
$router->map('GET', '/post', 'PostController@index', 'post.list');
$router->map('GET', '/post/{slug}-{id}', 'PostController@show', 'post.show');


$router->get('/foo', function () {
   echo 'Hello Friend';
});


echo $router->generate('home') . '<br>';
echo $router->generate('post.show', ['slug' => 'article-du-jour', 'id' => 1]) . '<br>';
echo $router->generate('admin/users', ['page' => 1, 'sort_type' => 'asc', 'sort_name' => 'name', 'direction' => 'asc']);
*/

$router->isPrettyUrl(false);

$options = [
    'namespace' => 'Admin',
    'prefix' => 'admin'
];

$router->namespace('Admin', function () use($router) {
    $router->map('GET', '/', 'HomeController@index', 'home');
    $router->map('GET', '/post', 'PostController@index', 'post.list');
});

$router->prefix('admin', function () use($router) {
    $router->map('GET', '/', 'HomeController@index', 'home');
    $router->map('GET', '/post', 'PostController@index', 'post.list');
});


dump($router->getNamedRoutes());
dump($router->getRoutes());


$route = $router->match($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

?>
    <a href="/post/index.php?page=1">Posts</a>
<?php
dd($route);
