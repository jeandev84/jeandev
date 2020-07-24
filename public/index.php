<?php


use Jan\Component\Routing\Router;

require_once __DIR__.'/../vendor/autoload.php';


$router = new Router();

$router->map('GET', '/', 'HomeController@index', 'home');
$router->map('GET', '/about', 'HomeController@about', 'about');
$router->map('GET|POST', '/contact', 'HomeController@contact', 'contact');


dd($router->routes());
