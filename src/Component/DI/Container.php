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
     * @var bool
    */
    private $autowire = true;


    /**
     * @var bool
    */
    private $booted  = false;


    /**
     * @var array
    */
    protected $calls = [];



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
        return $this->binded($abstract)
               || $this->hasInstance($abstract)
               || $this->isAlias($abstract);
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
     * @param $abstract
     * @param null $concrete
     * @param bool $singleton
     * @return Container
    */
    public function bind($abstract, $concrete = null, bool $singleton = false)
    {
          if(is_null($concrete))
          {
              $concrete = $abstract;
          }

          $this->bindings[$abstract] = compact('concrete', 'singleton');

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

         /*
            $concrete = $this->bindings[$abstract]['concrete'];

            if($concrete instanceof \Closure)
            {
                return $concrete($this);
            }

            return $concrete;
         */
    }


    /**
     * @param $abstract
     * @return bool
    */
    public function isSingleton($abstract)
    {
        return isset($this->bindings[$abstract]['singleton'])
               && $this->bindings[$abstract]['singleton'] === true;
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
        $this->runServiceProvider(
            $this->resolveProvider($provider)
        );

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
     * @param string $abstract
     * @param string $method
     * @param array $arguments
     * @return Container
    */
    public function rebootingCall(string $abstract, string $method, $arguments = [])
    {
          $this->calls[$abstract][] = [$abstract, $method, $arguments];

          return $this;
    }



    /**
     * @param $abstract
     * @return bool
    */
    public function hasCall($abstract)
    {
        return \array_key_exists($abstract, $this->calls);
    }



    /**
     * @param null $abstract
     * @return array|mixed
    */
    public function getCall($abstract)
    {
         if($this->hasCall($abstract))
         {
             return $this->calls[$abstract];
         }

         return [];
    }


    /**
     * @return array
    */
    public function getCalls()
    {
        return $this->calls;
    }


    /**
     * @param $callback
     * @param array $arguments
     * @param $method
     * @return mixed
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
     */
    public function call($callback, array $arguments = [], $method = null)
    {
        if(! \is_callable($callback))
        {
            if(is_object($callback))
            {
                $callback = get_class($callback);
            }

            if($this->autowire)
            {
                $reflectedMethod = new ReflectionMethod($callback, $method);
                $arguments = $this->resolveDependencies($reflectedMethod, $arguments);
            }

            $object = $this->get($callback);

            if(method_exists($object, $method))
            {
                $callback = [$object, $method];
            }
        }

        return call_user_func_array($callback, $arguments);
    }


    /**
     * @param $callback
     * @param null $method
     * @return array|callable
    */
    public function resolveCallback($callback, $method = null)
    {
        if(is_callable($callback) && ! $method)
        {
            return $callback;
        }

        return [$callback, $method];
    }

    /**
     * @param $id
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    public function boot($id)
    {
        foreach ($this->getCall($id) as $call)
        {
            list($abstract, $method, $arguments) = $call;

            $this->callAction($abstract, $method, $arguments);
        }
    }



    /**
     * Boot all calls
    */
    public function calls()
    {
        $bootIds = array_keys($this->getCalls());

        foreach ($bootIds as $id)
        {
            $this->boot($id);
        }
    }




    /**
     * @param $provider
     * @return bool|ServiceProvider|mixed|object
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    private function resolveProvider($provider)
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