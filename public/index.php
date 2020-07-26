<?php


use Jan\Component\Routing\Route;
use Jan\Component\Routing\Router;

require_once __DIR__.'/../vendor/autoload.php';


$router = new Router($_SERVER['HTTP_HOST']);


$router->map('GET', '/', 'HomeController@index', 'home');
$router->map('GET', '/about', 'HomeController@about', 'about');
$router->map('GET|POST', '/contact', 'HomeController@contact', 'contact');
$router->map('GET', '/post/{slug}/{id}', 'HomeController@contact', 'post.show');


$router->get('/foo', function () {
   echo 'Hello Friend';
});


echo $router->generate('home') . '<br>';
echo $router->generate('post.show', ['slug' => 'article-du-jour', 'id' => 1]);

dump($router->getNamedRoutes());
dump($router->getRoutes());


$route = $router->match($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);

dd($route);
