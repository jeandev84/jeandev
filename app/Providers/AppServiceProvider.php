<?php
namespace App\Providers;


use App\Foo;
use Jan\Component\DI\Contracts\BootableServiceProvider;
use Jan\Component\DI\ServiceProvider\ServiceProvider;


/**
 * Class AppServiceProvider
 * @package App\Providers
*/
class AppServiceProvider extends ServiceProvider implements BootableServiceProvider
{

    public $provides = [
       'foo',
       Foo::class,
       'something'
    ];


    public function register()
    {
        $this->container->singleton('foo', function ($container) {
            return $container->make(Foo::class);
        });
    }

    public function boot()
    {
        // so nothing
    }
}