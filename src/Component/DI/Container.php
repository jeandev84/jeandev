<?php
namespace Jan\Component\DI;


use Jan\Component\DI\Contracts\BootableServiceProvider;
use Jan\Component\DI\Contracts\ContainerInterface;
use Jan\Component\DI\Exceptions\ContainerException;
use Jan\Component\DI\Exceptions\ResolverDependencyException;
use Jan\Component\DI\ServiceProvider\ServiceProvider;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;



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
     * @var array
    */
    protected $bindings = [];


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
    protected $instances = [];



    /**
     * @var array
    */
    protected $aliases = [];



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
     * Determine if id bounded
     * @param $id
     * @return bool
    */
    public function bounded($id)
    {
        return isset($this->bindings[$id]);
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
     * Set instance
     *
     * @param $abstract
     * @param $instance
    */
    public function instance($abstract, $instance)
    {
        $this->instances[$abstract] = $instance;
    }


    /**
     * @param $instance
    */
    public function setInstance($instance)
    {
        $this->instances[get_class($instance)] = $instance;
    }



    /**
     * @param $alias
     * @param $original
    */
    public function setAlias($alias, $original)
    {
          $this->aliases[$alias] = $original;
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
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    public function make($abstract, $parameters = [])
    {
        return $this->resolve($abstract, $parameters);
    }


    /**
     * @param $abstract
     * @return bool|object
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    public function factory($abstract)
    {
        return $this->make($abstract);
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
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    public function getConcrete($abstract)
    {
         $concrete = $this->resolveConcrete($abstract);

         if(is_string($concrete))
         {
             if(! isset($this->instances[$concrete]))
             {
                 return $this->resolve($concrete);
             }

             return $this->instances[$concrete];
         }

         if($this->isSingleton($abstract))
         {
              return $this->getSingleton($abstract, $concrete);
         }

         return $concrete;
    }


    /**
     * @param $abstract
     * @return bool
    */
    public function isSingleton($abstract)
    {
        return isset($this->bindings[$abstract]['shared'])
               && $this->bindings[$abstract]['shared'] === true;
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
     * @param $abstract
     * @return mixed
    */
    protected function resolveConcrete($abstract)
    {
        $concrete = $this->bindings[$abstract]['concrete'];

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
        if(! isset($this->aliases[$abstract]) && ! class_exists($abstract))
        {
             return $abstract;
        }

        if(isset($this->aliases[$abstract]))
        {
            $abstract = $this->aliases[$abstract];
        }


        $reflectedClass = new ReflectionClass($abstract);

        if(! $reflectedClass->isInstantiable())
        {
            throw new ContainerException(
                sprintf('[%s] is not instantiable dependency.', $abstract)
            );
        }

        return $this->instances[$abstract] = $this->resolveInstance($reflectedClass, $arguments);
    }


    /**
     * @param ReflectionClass $reflectedClass
     * @param array $arguments
     * @return object|bool
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    protected function resolveInstance(ReflectionClass $reflectedClass, $arguments = [])
    {
        if(! $constructor = $reflectedClass->getConstructor())
        {
             return $reflectedClass->newInstance();
        }

        $dependencies = $this->resolveDependencies($constructor, $arguments);
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
    public function resolveDependencies(ReflectionMethod $reflectionMethod, $arguments = [])
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
        if($this->bounded($id))
        {
            return true;
        }

        return false;
    }



    /**
     * Add Service Provider
     * @param string|ServiceProvider $provider
     * @return Container
     *
     *  Example:
     *  $this->addServiceProvider(new \App\Providers\AppServiceProvider());
     *  $this->addServiceProvider(App\Providers\AppServiceProvider::class);
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
     */
    public function addServiceProvider($provider)
    {
        $this->runServiceProvider(
            $this->resolveProvider($provider)
        );

        return $this;
    }


    /**
     * @param $provider
     * @return bool|ServiceProvider|mixed|object
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    protected function resolveProvider($provider)
    {
         if(is_string($provider))
         {
             $provider = $this->resolve($provider);
         }

         if(! $provider instanceof ServiceProvider)
         {
             throw new ContainerException(
                 sprintf('Class %s is not instance of ServiceProvider', get_class($provider))
             );
         }

         return $provider;
    }


    /**
     * @param array $providers
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
     */
    public function addServiceProviders(array $providers)
    {
        foreach ($providers as $provider)
        {
            $this->addServiceProvider($provider);
        }
    }


    /**
     * @param ServiceProvider $provider
     * @throws ContainerException
    */
    public function runServiceProvider(ServiceProvider $provider)
    {
        if(! in_array($provider, $this->providers))
        {
            $provider->setContainer($this);

            if($provides = $provider->getProvides())
            {
                foreach ($provides as $provide)
                {
                    if(! \array_key_exists($provide, $this->aliases))
                    {
                        throw new ContainerException('Can not resolve this alias!');
                    }

                    //
                }

                $this->provides[] = $provides;
            }

            $implements = class_implements($provider);

            if(isset($implements[BootableServiceProvider::class]))
            {
                 /* $provider->boot(); */
                 $this->call($provider, 'boot');
            }

            /* $provider->register(); */
            $this->call($provider, 'register');
            $this->providers[] = $provider;
        }
    }


    /**
     * @param object $object $object
     * @param string $method
     * @param array $arguments
    */
    public function call($object, $method, $arguments = [])
    {
         if(is_callable($object, $method))
         {
             $object->{$method}(...$arguments);
         }
    }


    /**
     * @param mixed $offset
     * @return bool
    */
    public function offsetExists($offset)
    {
        return $this->has($offset);
    }



    /**
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->get($offset);
    }



    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        $this->bind($offset, $value);
    }


    /**
     * @param mixed $offset
    */
    public function offsetUnset($offset)
    {
        unset($this->bindings[$offset]);
    }
}