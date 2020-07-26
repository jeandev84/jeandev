<?php
namespace Jan\Component\DI;


use Jan\Component\DI\Contracts\ContainerInterface;
use Jan\Component\DI\Exceptions\ContainerException;
use Jan\Component\DI\Exceptions\ResolverDependencyException;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

/**
 * Class Container
 * @package Jan\Component\DI
*/
class Container implements ContainerInterface
{


    /**
     * @var Container
    */
    protected static $instance;


    /**
     * @var array
    */
    protected $bindings = [];


    /**
     * @var array
    */
    protected $instances = [];



    /**
     * Get container instance
     *
     * @return Container
    */
    public static function getInstance()
    {
         if(is_null(static::$instance))
         {
              static::$instance = new static();
         }

         return static::$instance;
    }


    /**
     * @param $abstract
     * @param null $concrete
     * @param bool $shared
     * @return Container
    */
    public function bind($abstract, $concrete = null, $shared = false)
    {
          if(is_null($concrete))
          {
              $concrete = $abstract;
          }

          $this->bindings[$abstract] = compact('concrete', 'shared');

          return $this;
    }



    /**
     * @return array
    */
    public function getBindings()
    {
        return $this->bindings;
    }


    /**
     * Bind from configuration
     *
     * @param array $configs
     * @return Container
    */
    public function bindings(array $configs)
    {
        foreach ($configs as $config)
        {
            list($abstract, $concrete, $singleton) = $config;
            $this->bind($abstract, $concrete, $singleton);
        }

        return $this;
    }


    /**
     * @param $abstract
     * @param $concrete
    */
    public function singleton($abstract, $concrete)
    {
         $this->bind($abstract, $concrete, true);
    }




    /**
     * Create new instance of object wit given params
     *
     * @param $abstract
     * @param array $parameters
     * @return object
     * @throws ReflectionException
   */
    public function make($abstract, $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }


    /**
     * Get concrete form container
     *
     *
     * @param $abstract
     * @param array $arguments
     * @return mixed|void
     * @throws Exceptions\ContainerException
     * @throws Exceptions\ResolverDependencyException
     * @throws \ReflectionException
    */
    public function get($abstract, $arguments = [])
    {
        if(! $this->has($abstract))
        {
            return $this->resolve($abstract, $arguments);
        }

        return $this->getConcrete($abstract);
    }


    /**
     * @param $abstract
     * @return mixed
    */
    public function getConcrete($abstract)
    {
         $concrete = $this->resolveConcrete(
             $this->bindings[$abstract]['concrete']
         );

         if(is_string($concrete) && ! isset($this->instances[$concrete]))
         {
             return $this->resolve($concrete);
         }

         if($this->bindings[$abstract]['shared'] === true)
         {
              return $this->getSingleton($abstract, $concrete);
         }

         return $concrete;
    }


    /**
     * @param $abstract
     * @param $concrete
     * @return mixed
    */
    public function getSingleton($abstract, $concrete)
    {
         if(! isset($this->instances[$abstract]))
         {
             $this->instances[$abstract] = $concrete;
         }

         return $this->instances[$abstract];
    }


    /**
     * @param $concrete
     * @return mixed
    */
    protected function resolveConcrete($concrete)
    {
        if($concrete instanceof \Closure)
        {
            return $concrete($this);
        }

        return $concrete;
    }


    /**
     * @param $abstract
     * @param $arguments
     * @return mixed
     * @throws Exceptions\ContainerException
     * @throws Exceptions\ResolverDependencyException
     * @throws ReflectionException
    */
    public function resolve($abstract, $arguments = [])
    {
        $reflectedClass = new ReflectionClass($abstract);

        if($reflectedClass->isInstantiable())
        {
            return $this->instances[$abstract] = $this->resolveInstance($reflectedClass, $arguments);
        }

        throw new ContainerException(
            sprintf('Class [%s] is not instantiable dependency.', $abstract)
        );
    }


    /**
     * @param ReflectionClass $reflectedClass
     * @param array $arguments
     * @return object|bool
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    private function resolveInstance(ReflectionClass $reflectedClass, $arguments = [])
    {
        if(! $constructor = $reflectedClass->getConstructor())
        {
             return $reflectedClass->newInstance();
        }

        $dependencies = $this->resolveMethodDependencies($constructor, $arguments);
        return $reflectedClass->newInstanceArgs($dependencies);
    }


    /**
     * Resolve method dependencies
     *
     * @param ReflectionMethod $reflectionMethod
     * @param array $arguments
     * @return array
     * @throws ReflectionException|ContainerException|ResolverDependencyException
     */
    public function resolveMethodDependencies(ReflectionMethod $reflectionMethod, $arguments = [])
    {
        $dependencies = [];

        foreach ($reflectionMethod->getParameters() as $parameter)
        {
            $dependency = $parameter->getClass();

            if($parameter->isOptional()) { continue; }
            if($parameter->isArray()) { continue; }

            if(is_null($dependency))
            {
                if($parameter->isDefaultValueAvailable())
                {
                    $dependencies[] = $parameter->getDefaultValue();
                }else{

                    if(array_key_exists($parameter->getName(), $arguments))
                    {
                        $dependencies[] = $arguments[$parameter->getName()];
                    }else {
                        $dependencies = array_merge($dependencies, $arguments);
                    }
                }

            } else{

                $dependencies[] = $this->get($dependency->getName());
            }
        }

        return $dependencies;
    }



    /**
     * @param $id
     * @return bool
    */
    public function has($id)
    {
        if(isset($this->bindings[$id]))
        {
            return true;
        }

        return false;
    }
}