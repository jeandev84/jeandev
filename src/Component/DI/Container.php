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



/**
 * Class Container
 * @package Jan\Component\DI
*/
class Container implements \ArrayAccess, ContainerInterface
{
    
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
    protected $closures = [];
    
    
    /**
     * @var Container
    */
    protected static $instance;



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
               $concrete =  $concrete($this);
          }

          $this->bindings[$abstract] = compact('concrete', 'shared');

          return $this;
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
     * Determine if id binded
     * @param $id
     * @return bool
     */
    public function binded($id)
    {
        return isset($this->bindings[$id]);
    }


    /**
     * @param $abstract
     * @return bool
     */
    public function bound($abstract)
    {
        return isset($this->bindings[$abstract])
            || isset($this->instances[$abstract])
            || isset($this->resolved[$abstract]);
    }


    /**
     * @param $abstract
     * @return bool
     */
    public function isResolved($abstract)
    {
        return isset($this->resolved[$abstract]);
    }


    /**
     * @param $abstract
     * @return mixed
     */
    public function getResolved($abstract)
    {
        return $this->resolved[$abstract];
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
            list($abstract, $concrete, $shared) = $config;

            $this->bind($abstract, $concrete, $shared);
        }

        return $this;
    }


    /**
     * Set instance
     *
     * @param $abstract
     * @param object $instance
    */
    public function instance($abstract, $instance)
    {
        $this->instances[$abstract] = $instance;
    }


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
     * @param ContainerInterface|null $container
     * @return ContainerInterface
    */
    public static function setInstance(ContainerInterface $container = null)
    {
        return static::$instance = $container;
    }


    /**
     * @param $name
     * @param $original
    */
    public function setAlias($name, $original)
    {
          $this->aliases[$name] = $original;
    }


    /**
     * @param $abstract
     * @return mixed
     */
    public function getAlias($abstract)
    {
        if(isset($this->aliases[$abstract]))
        {
            return $this->aliases[$abstract];
        }

        return $abstract;
    }



    /**
     * @param $name
     * @return bool
    */
    public function isAlias($name)
    {
        return isset($this->aliases[$name]);
    }



    /**
     * @param array $aliases
    */
    public function setAliases(array $aliases)
    {
        foreach ($aliases as $name => $original)
        {
             $this->setAlias($name, $original);
        }
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
    */
    public function make($abstract, $parameters = [])
    {
        return function () use ($abstract, $parameters) {

             return $this->resolve($abstract, $parameters);
        };
    }


    /**
     * @param $abstract
     * @return bool|object
    */
    public function factory($abstract)
    {
        return function () use ($abstract) {

            return $this->make($abstract);
        };
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
        if($this->hasConcrete($abstract))
        {
            return $this->getConcrete($abstract);
        }

        return $this->resolve($abstract, $arguments);
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
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    public function getConcrete($abstract)
    {
         $concrete = $this->bindings[$abstract]['concrete'];

         if(is_string($concrete) && class_exists($concrete))
         {
             $concrete = $this->resolve($concrete);
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
     * @param $concrete
    */
    public function share($abstract, $concrete)
    {
        $this->instances[$abstract] = function () use ($abstract, $concrete){

             if(! isset($this->shared[$abstract]))
             {
                 $this->shared[$abstract] = $concrete;

                 if($this->isClosure($concrete))
                 {
                     $this->shared[$abstract] = $concrete();
                 }
             }

             return $this->shared[$abstract];
        };
    }




    /**
     * @param $abstract
     * @param $concrete
     * @return mixed
    */
    protected function getSingleton($abstract, $concrete)
    {
         if(! isset($this->instances[$abstract]))
         {
             $this->instances[$abstract] = $concrete;
         }

         return $this->instances[$abstract];
    }



    /**
     * @param $abstract
     * @return bool
    */
    public function isInstantiated($abstract)
    {
         return isset($this->instances[$abstract]);
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
        $abstract = $this->getAlias($abstract);

        if($this->isResolved($abstract))
        {
            return $this->getResolved($abstract);
        }


        if($this->isInstantiated($abstract))
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

        $instance = $this->makeInstance($reflectedClass, $arguments);
        return $this->instances[$abstract] = $this->resolved[$abstract] = $instance;
    }



    /**
     * @param ReflectionClass $reflectedClass
     * @param array $arguments
     * @return object|bool
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    protected function makeInstance(ReflectionClass $reflectedClass, $arguments = [])
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
        return $this->bound($id);
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
        
        $this->runServiceProvider($provider);

        return $this;
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
     * @param ServiceProvider $serviceProvider
     * @return array
    */
    public function getServiceProvides(ServiceProvider $serviceProvider)
    {
         return $serviceProvider->getProvides();
    }


    /**
     * @param array $provides
     * @return $this
    */
    public function setProvides(array $provides)
    {
         $this->provides = array_merge($this->provides, $provides);

         return $this;
    }


    /**
     * @return array
    */
    public function getProvides()
    {
         return $this->provides;
    }



    /**
     * @param ServiceProvider $provider
     * @throws ContainerException
    */
    public function runServiceProvider(ServiceProvider $provider)
    {
        $provider->setContainer($this);
        $abstract = get_class($provider);

        if(! \in_array($provider, $this->providers))
        {

            $implements = class_implements($provider);
            
            if(isset($implements[BootableServiceProvider::class]))
            {
                 $provider->boot();
            }

            $provider->register();
            $this->providers[] = $provider;
        }
    }

    
    

    /**
     * @param ServiceProvider $provider
     * @throws ContainerException
    */
    protected function resolvedProvides(ServiceProvider $provider)
    {
        if($provides = $provider->getProvides())
        {
            foreach ($provides as $provide)
            {
                if(! isset($this->aliases[$provide]))
                {
                    throw new ContainerException(
                        sprintf('Can not resolve this alias %s', $provide)
                    );
                }
            }

            $this->setProvides($provider->getProvides());
        }
    }
    
    
    
    /**
     * @param $abstract
     * @param array $arguments
     * @param $method
     * @return mixed
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    public function call($abstract, array $arguments = [], $method = null)
    {
        if(! \is_callable($abstract))
        {
            if(is_object($abstract))
            {
                $abstract = get_class($abstract);
            }

            if($this->autowire)
            {
                $reflectedMethod = new ReflectionMethod($abstract, $method);
                $arguments = $this->resolveDependencies($reflectedMethod, $arguments);
            }

            $object = $this->get($abstract);

            if(method_exists($object, $method))
            {
                return $this->callback([$object, $method], $arguments);
            }
        }

        if(! $method)
        {
            return $this->callback($abstract, $arguments);
        }
    }




    /**
     * @param $callable
     * @param array $arguments
     * @return mixed
     * @throws ReflectionException
    */
    public function callback($callable, array $arguments = [])
    {
         return call_user_func_array($callable, $arguments);
    }



    /**
     * @param $concrete
     * @return bool
    */
    public function isClosure($concrete)
    {
        return $concrete instanceof Closure;
    }



    /**
     * @param $callable
     * @throws ReflectionException
    */
    public function resolveFunctionDependencies($callable)
    {
        $reflectedFunction = new \ReflectionFunction($callable);
    }



    /**
     * @param $abstract
     * @param $method
     * @param array $arguments
     * @return $this
    */
    public function feedBack($abstract, $arguments = [], $method = null)
    {
        $this->calls[$abstract][] = [$abstract, $arguments, $method];

        return $this;
    }


    /**
     * @param $name
     * @param Closure $closure
     * @return $this
    */
    public function closure($name, \Closure $closure)
    {
         $this->closures[$name] = $closure;
         
         return $this;
    }


    /**
     * @param $abstract
     * @return bool
    */
    public function isBooted($abstract)
    {
        return \array_key_exists($abstract, $this->calls)
              || \array_key_exists($abstract, $this->closures);
    }



    /**
     * @param $abstract
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    public function bootFeedback($abstract)
    {
        if(\array_key_exists($abstract, $this->calls))
        {
            $callParams = $this->calls[$abstract];

            if($callParams)
            {
                foreach ($callParams as $callback)
                {
                    list($abstract, $parameters, $method) = $callback;
                    $this->call($abstract, $parameters, $method);
                }
            }
        }
    }


    /**
     * @param $abstract
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    public function bootClosure($abstract)
    {
        if(\array_key_exists($abstract, $this->closures))
        {
            $this->call($this->closures[$abstract]);
        }
    }


    /**
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    public function calling()
    {
        $callbacks = array_merge($this->getClosures(), $this->getCalls());

        foreach (array_keys($callbacks) as $id)
        {
            $this->bootFeedback($id);
            $this->bootClosure($id);
        }
    }


    /**
     * @return array
    */
    public function getCalls()
    {
        return $this->calls;
    }


    /**
     * @return array
    */
    public function getClosures()
    {
       return $this->closures;
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
        unset($this->bindings[$offset], $this->instances[$offset]);
    }


    /**
     * @param $name
     * @return array|bool|mixed|object|string|null
    */
    public function __get($name)
    {
        return $this[$name];
    }


    /**
     * @param $name
     * @param $value
    */
    public function __set($name, $value)
    {
        $this[$name] = $value;
    }
}