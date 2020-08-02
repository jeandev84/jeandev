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

          if($this->canResolveConcrete($concrete))
          {
              $concrete = $this->makeInstance($concrete);
          }
          
          $this->bindings[$abstract] = compact('concrete', 'shared');

          return $this;
    }


    /**
     * @param $concrete
     * @return bool
    */
    public function canResolveConcrete($concrete)
    {
        return is_string($concrete) && class_exists($concrete);
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
     * Create new instance of object wit given params
     *
     * @param $abstract
     * @param array $parameters
     * @return object
    */
    public function make($abstract, $parameters = [])
    {
        return (function () use ($abstract, $parameters) {

            return $this->resolve($abstract, $parameters);

        })();
    }


    /**
     * @param $abstract
     * @return bool|object
    */
    public function factory($abstract)
    {
        return (function () use ($abstract) {

            return $this->make($abstract);

        })();
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
     * @return bool
    */
    public function isShared($abstract)
    {
        return isset($this->bindings[$abstract]['shared']);
    }


    /**
     * @param $abstract
     * @param object $instance
     */
    public function instance($abstract, $instance)
    {
         $this->instances[$abstract] = $instance;
    }


    /**
     * @param $abstract
     * @return mixed
    */
    public function getConcreteInstance($abstract)
    {
         return $this->instances[$abstract];
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
        try {

            return $this->resolve($id, $arguments);

        } catch (ContainerException $e) {

            throw $e;
        }
    }


    /**
     * @param $abstract
     * @param array $arguments
    */
    public function resolve($abstract, $arguments = [])
    {
        $abstract = $this->getAlias($abstract);

        $concrete = $this->getConcrete($abstract);

        if($this->isBounded($abstract))
        {
            return $concrete;
        }

        if($this->isSingleton($abstract))
        {
             $this->instance($abstract, $concrete);
        }

        if($this->hasInstance($abstract))
        {
            return $this->instances[$abstract];
        }

        return $this->resolved[$abstract] = $this->makeInstance($abstract, $arguments);
    }


    /**
     * @param $abstract
     * @return bool
    */
    protected function isBounded($abstract)
    {
        return isset($this->bindings[$abstract]['shared'])
               && $this->bindings[$abstract]['shared'] === false;
    }


    /**
     * @param $abstract
     * @param array $arguments
     * @return object
    */
    public function makeInstance($abstract, $arguments = [])
    {
        $reflectedClass = new ReflectionClass($abstract);

        if(! $reflectedClass->isInstantiable())
        {
            foreach ($this->instances as $instance)
            {
                //dump($id, $instance);
                $implements = class_implements($instance);

                if(! isset($implements[$abstract]))
                {
                    throw new ContainerException(
                        sprintf('Can not get instance of %s', $abstract)
                    );
                }

                return $instance;
            }
        }

        if(! $constructor = $reflectedClass->getConstructor())
        {
            return $reflectedClass->newInstance();
        }


        $dependencies = $this->resolveMethodDependencies($constructor, $arguments);
        return $reflectedClass->newInstanceArgs($dependencies);
    }



    /**
     * @param ReflectionMethod $reflectionMethod
     * @param array $arguments
     * @return ReflectionParameter[]
    */
    public function resolveMethodDependencies(ReflectionMethod  $reflectionMethod, $arguments = [])
    {
        $dependencies = [];

        foreach ($reflectionMethod->getParameters() as $parameter) {
            $dependency = $parameter->getClass();

            if ($parameter->isOptional()) {
                continue;
            }
            if ($parameter->isArray()) {
                continue;
            }

            if (is_null($dependency)) {
                if ($parameter->isDefaultValueAvailable()) {
                    $dependencies[] = $parameter->getDefaultValue();
                } else {

                    if (array_key_exists($parameter->getName(), $arguments)) {
                        $dependencies[] = $arguments[$parameter->getName()];
                    } else {
                        $dependencies = array_merge($dependencies, $arguments);
                    }
                }

            } else {

                $dependencies[] = $this->get($dependency->getName());
            }
        }

        return $dependencies;
    }


    /**
     * @param Closure $closure
     * @return array
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
     */
    public function resolveFunctionDependencies(Closure $closure)
    {
        $reflectedFunction = new \ReflectionFunction($closure);

        $dependencies = [];

        foreach ($reflectedFunction->getParameters() as $parameter)
        {
            $dependencies[] = $this->resolve($parameter->getType()->getName());
        }

        $dependencies[] = $this;

        return $dependencies;

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