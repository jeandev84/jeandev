<?php

use Jan\Component\Http\Request;
use Jan\Component\Http\Response;
use Jan\Component\Routing\Route;
use Jan\Component\Routing\Router;

require_once __DIR__.'/../vendor/autoload.php';


$container = new \Jan\Component\DI\Container();

$container->bind('db', 'some database');

dd($container->get('db'));

/*
$container->instance(\Jan\Component\DI\Container::class, $container);
$container->singleton(\App\Foo::class, \App\Foo::class);

$container->calling();
dd($container);
*/