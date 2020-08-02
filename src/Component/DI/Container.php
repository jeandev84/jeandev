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
     * @param ContainerInterface|null $container
     * @return ContainerInterface
    */
    public static function setInstance(ContainerInterface $container = null)
    {
        return static::$instance = $container;
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

        $concrete = $this->resolveConcrete($concrete);
        $this->bindings[$abstract] = compact('concrete', 'shared');

        return $this;
    }


    /**
     * @param $concrete
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    public function resolveConcrete($concrete)
    {
        if($concrete instanceof Closure)
        {
            return $concrete($this); 
        }

        if(class_exists($concrete))
        {
            return $this->resolveInstance($concrete);
        }

        return $concrete;
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
     * @param array $binds
    */
    public function binds(array $binds)
    {
        foreach ($binds as $bindParam)
        {
            list($abstract, $concrete, $shared) = $bindParam;

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
     * @param $concrete
     * @return Closure
     */
    public function share($abstract, $concrete)
    {
        $this->instances[$abstract] = (function () use ($abstract, $concrete){

            if(! isset($this->shared[$abstract]))
            {
                $this->shared[$abstract] = $this->resolveConcrete($concrete);
            }

            return $this->shared[$abstract];

        })();
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
     * @param array $aliases
     */
    public function aliases(array $aliases)
    {
        foreach ($aliases as $name => $original)
        {
            $this->alias($name, $original);
        }
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

        return $this->resolved[$abstract] = $this->resolveInstance($abstract, $arguments);
    }


    /**
     * @param $abstract
     * @return bool
    */
    public function isBounded($abstract)
    {
        return isset($this->bindings[$abstract]['shared'])
               && $this->bindings[$abstract]['shared'] === false;
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
            return $this->getInstanceFromImplemented($abstract);
        }

        if(! $constructor = $reflectedClass->getConstructor())
        {
            return $reflectedClass->newInstance();
        }

        $dependencies = $this->resolveMethodDependencies($constructor, $arguments);
        return $reflectedClass->newInstanceArgs($dependencies);
    }


    /**
     * @param $abstract
     * @return mixed
     * @throws ContainerException
    */
    public function getInstanceFromImplemented($abstract)
    {
        foreach ($this->instances as $instance)
        {
            $implements = class_implements($instance);

            if(isset($implements[$abstract]) || $instance instanceof $abstract)
            {
                 return $instance;
            }
        }

        throw new ContainerException(
            sprintf('Cannot resolve instance of %s', $abstract)
        );
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
       if(! is_callable($abstract) && $method)
       {
           if(is_object($abstract))
           {
               $abstract = get_class($abstract);
           }

           $arguments = $this->resolveMethodDependencies(
               new ReflectionMethod($abstract, $method),
               $arguments
           );

           $object = $this->get($abstract);

           if(method_exists($object, $method))
           {
               return $this->calling([$object, $method], $arguments);
           }
       }

       return $this->calling($abstract, $arguments);
    }




    /**
     * @param $abstract
     * @param $method
     * @param array $arguments
     * @return $this
     */
    public function bindCalling($abstract, $arguments = [], $method = null)
    {
        $this->calls[$abstract][] = [$abstract, $arguments, $method];

        return $this;
    }


    /**
     * Boot calls method
     * @param string $id
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
     */
    public function callMethod(string $id)
    {
        if($callParams = $this->called($id))
        {
            foreach ($callParams as $callParam)
            {
                list($abstract, $parameters, $method) = $callParam;

                $this->call($abstract, $parameters, $method);
            }
        }
    }


    /**
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    public function callMethods()
    {
        foreach (array_keys($this->calls) as $id)
        {
            $this->callMethod($id);
        }
    }




    /**
     * @param string $id
     * @return array|mixed
    */
    public function called(string $id)
    {
         return $this->calls[$id] ?? [];
    }



    /**
     * @param Closure $closure
     * @return void
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    public function closure(\Closure $closure)
    {
        $dependencies = $this->resolveFunctionDependencies($closure);
        $closure(...$dependencies);
    }



    /**
     * @param $callable
     * @param array $arguments
     * @return mixed
     * @throws ReflectionException
     */
    public function calling($callable, array $arguments = [])
    {
        if($callable instanceof Closure)
        {
            $arguments = array_merge($this->resolveFunctionDependencies($callable), $arguments);
        }

        return call_user_func_array($callable, $arguments);
    }


    /**
     * @param $concrete
     * @return mixed
     * @throws ContainerException
     * @throws ReflectionException
     * @throws ResolverDependencyException
    */
    public function getClosure($concrete)
    {
        $dependencies = $this->resolveFunctionDependencies($concrete);
        return $concrete(...$dependencies);
    }




    /**
     * @param ServiceProvider $provider
     * @throws ContainerException
    */
    protected function resolveProvides(ServiceProvider $provider)
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
            $provider = $this->resolveInstance($provider);
        }

        if($provider instanceof ServiceProvider)
        {
            $provider->setContainer($this);

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