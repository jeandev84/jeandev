<?php
namespace Jan\Component\DI;


use Closure;
use Jan\Component\DI\Contracts\BootableServiceProvider;
use Jan\Component\DI\Contracts\ContainerInterface;
use Jan\Component\DI\Exceptions\ContainerException;
use Jan\Component\DI\Exceptions\ResolverDependencyException;
use Jan\Component\DI\ServiceProvider\ServiceProvider;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use ReflectionParameter;


/**
 * Class Container
 * @package Jan\Component\DI
*/
class Container implements \ArrayAccess, ContainerInterface
{

    /**
     * @var Container
    */
    protected static $instance;


    /**
     * @var bool
    */
    private $autowire = true;


    /**
     * @var array
    */
    protected $shared = [];



    /**
     * @var array
    */
    protected $calls = [];



    /**
     * @var array
    */
    protected $resolved = [];


    /**
     * @var array
    */
    protected $bindings = [];



    /**
     * @var array
    */
    protected $instances = [];


    /**
     * @var array
    */
    protected $providers = [];


    /**
     * @var array
    */
    protected $provides  = [];



    /**
     * @var array
    */
    protected $aliases = [];



    /**
     * @return Container
    */
    public static function getInstance()
    {
        if(! static::$instance)
        {
            static::$instance = new static();
        }

        return static::$instance;
    }



    /**
     * @param bool $status
     * @return $this
    */
    public function autowire(bool $status)
    {
        $this->autowire = $status;

        return $this;
    }



    /**
     * @param $abstract
     * @param null $concrete
     * @param bool $shared
     * @return Container
    */
    public function bind($abstract, $concrete = null, bool $shared = false)
    {
          if(is_null($concrete))
          {
              $concrete = $abstract;
          }

          if($concrete instanceof Closure)
          {
               $concrete = $concrete($this);
          }

          $this->bindings[$abstract] = compact('concrete', 'shared');

          return $this;
    }


    /**
     * @param $abstract
     * @return bool
    */
    public function bound($abstract)
    {
        return isset($this->bindings[$abstract]);
    }


    /**
     * @param $abstract
    */
    public function rebound($abstract)
    {

    }



    /**
     * @param array $binds
    */
    public function binds(array $binds)
    {
        foreach ($binds as $bind)
        {
            list($abstract, $concrete, $shared) = $bind;
            $this->bind($abstract, $concrete, $shared);
        }
    }



    /**
     * @return array
    */
    public function getBindings()
    {
        return $this->bindings;
    }



    /**
     * @param $abstract
     * @param $concrete
    */
    public function singleton($abstract, $concrete = null)
    {
         $this->bind($abstract, $concrete, true);
    }



    /**
     * @param $abstract
     * @return bool
    */
    public function isSingleton($abstract)
    {
        return $this->isShared($abstract)
               && $this->bindings[$abstract]['shared'] === true;
    }


    /**
     * @param $abstract
     * @param $concrete
     * @return void
    */
    public function share($abstract, $concrete)
    {
        $this->instances[$abstract] = (function () use ($abstract, $concrete){

            if(! isset($this->shared[$abstract]))
            {
                $this->shared[$abstract] = $concrete;
            }

            return $this->shared[$abstract];

        })();
    }



    /**
     * @param $abstract
     * @return bool
    */
    public function isShared($abstract)
    {
        return isset($this->bindings[$abstract]['shared']);
    }



    /**
     * @param $abstract
     * @param $instance
    */
    public function instance($abstract, $instance)
    {
         $this->instances[$abstract] = $instance;
    }


    /**
     * @param $abstract
     * @return bool
    */
    public function hasInstance($abstract)
    {
        return isset($this->instances[$abstract]);
    }



    /**
     * @return array
    */
    public function getInstances()
    {
        return $this->instances;
    }



    /**
     * @param $name
     * @param $original
    */
    public function alias($name, $original)
    {
        $this->aliases[$name] = $original;
    }


    /**
     * @param $name
     * @return bool
    */
    public function hasAlias($name)
    {
        return isset($this->aliases[$name]);
    }


    /**
     * @param $name
     * @return mixed
    */
    public function getAlias($name)
    {
        if($this->hasAlias($name))
        {
            return $this->aliases[$name];
        }

        return $name;
    }


    /**
     * @param $abstract
     */
    public function getContextualConcrete($abstract)
    {
        if(! $this->isShared($abstract))
        {

        }
    }


    /**
     * @param $abstract
     * @return bool
    */
    public function hasConcrete($abstract)
    {
        return isset($this->bindings[$abstract])
               && isset($this->bindings[$abstract]['concrete']);
    }



    /**
     * @param $abstract
     * @return mixed
    */
    public function getConcrete($abstract)
    {
        if($this->hasConcrete($abstract))
        {
            return $this->bindings[$abstract]['concrete'];
        }

        return null;
    }



    /**
     * @param $id
     * @param array $arguments
     * @return mixed
    */
    public function get($id, $arguments = [])
    {
        if (! $this->has($id))
        {
             $this->bind($id);
        }

        return $this->resolve($id, $arguments);
    }


    /**
     * @param $abstract
     * @param array $arguments
    */
    public function resolve($abstract, $arguments = [])
    {
        $abstract = $this->getAlias($abstract);

        /* $concrete = $this->getContextualConcrete($abstract); */
        $concrete = $this->getConcrete($abstract);

        if($this->isSingleton($abstract))
        {
             $this->instance($abstract, $concrete);
        }

        if(! $this->isNeedResolution($abstract))
        {
             return $concrete;
        }

        return $this->resolveInstance($abstract, $arguments);
    }



    /**
     * @param $abstract
     * @return bool
    */
    public function isNeedResolution($abstract)
    {
        return class_exists($abstract);
    }


    /**
     * @param $abstract
     * @param array $arguments
     * @return object
    */
    public function resolveInstance($abstract, $arguments = [])
    {
        if($this->hasInstance($abstract))
        {
            return $this->instances[$abstract];
        }

        $reflectedClass = new ReflectionClass($abstract);

        if(! $reflectedClass->isInstantiable())
        {
            throw new ContainerException(
                sprintf('[%s] is not instantiable dependency.', $abstract)
            );
        }

        if(! $constructor = $reflectedClass->getConstructor())
        {
            return $this->resolved[$abstract] = $reflectedClass->newInstance();
        }

        $dependencies = $this->resolveMethodDependencies($constructor, $arguments);
        return $this->resolved[$abstract] =  $reflectedClass->newInstanceArgs($dependencies);
    }


    /**
     * @param ReflectionMethod $reflectionMethod
     * @param array $arguments
     * @return ReflectionParameter[]
    */
    public function resolveMethodDependencies(ReflectionMethod  $reflectionMethod, $arguments = [])
    {
         return array_filter($reflectionMethod->getParameters(), function ($parameter) use ($arguments){

             dd($parameter);

         }, ARRAY_FILTER_USE_KEY);
    }


    /**
     * @param ReflectionParameter $parameter
    */
    public function resolveMethodParameters(ReflectionParameter  $parameter)
    {

    }



    /**
     * @param $id
     * @return bool
    */
    public function has($id)
    {
         return isset($this->bindings[$id])
                || isset($this->instances[$id])
                || isset($this->resolved[$id]);
    }



    /**
     * @param mixed $id
     * @return bool
    */
    public function offsetExists($id)
    {
        return $this->has($id);
    }


    /**
     * @param mixed $id
     * @return mixed
    */
    public function offsetGet($id)
    {
        return $this->get($id);
    }


    /**
     * @param mixed $id
     * @param mixed $value
    */
    public function offsetSet($id, $value)
    {
        $this->bind($id, $value);
    }


    /**
     * @param mixed $id
    */
    public function offsetUnset($id)
    {
        unset(
          $this->bindings[$id],
          $this->instances[$id],
          $this->resolved[$id]
        );
    }
}