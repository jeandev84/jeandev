<?php

use Jan\Autoload\Autoloader;
use Jan\Component\Http\Request;
use Jan\Component\Http\Response;
use Jan\Component\Routing\Route;
use Jan\Component\Routing\Router;
use App\Entity\User;
use Jan\Worker;


function dump($arr, $die = false)
{
    echo '<pre>';
    print_r($arr);
    echo '</pre>';
    if($die) die;
}

// require_once __DIR__.'/../vendor/autoload.php';

require_once __DIR__.'/../src/Autoload/Autoloader.php';

$autoloader = Autoloader::load(__DIR__.'/../');

$autoloader->addNamespace('Jan\\', __DIR__ . '/../src');
$autoloader->addNamespace('App\\', __DIR__.'/../app');

$autoloader->register();


$user = new User();

dump($user->getRole());
dump($user->getRoles());

dump($autoloader->getMappedNamespaces());

$work = new Worker();

$work->run();