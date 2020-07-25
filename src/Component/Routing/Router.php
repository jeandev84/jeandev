<?php
namespace Jan\Component\Routing;


use Closure;
use Jan\Component\Routing\Exception\RouterException;
use RuntimeException;



/**
 * Class Router
 * @package Jan\Component\Routing
*/
class Router
{

      const OPTION_PARAM_PREFIX     = 'prefix';
      const OPTION_PARAM_NAMESPACE  = 'namespace';
      const OPTION_PARAM_MIDDLEWARE = 'middleware';


      /**
       * @var string
      */
      protected $baseUrl;


      /**
       * Current route
       *
       * @var Route
      */
      protected $route;


      /**
       * Route collection
       *
       * @var array
      */
      protected $routes = [];


      /**
       * Set named route
       *
       * @var array
      */
      protected $namedRoutes = [];



      /**
       * Route options params
       *
       * @var array
      */
      protected $options = [];



      /**
       * Router constructor.
       *
       * @param string $baseUrl
      */
      public function __construct(string $baseUrl = '')
      {
           if($baseUrl)
           {
               $this->setBaseUrl($baseUrl);
           }
      }




    /**
     * @return array
     */
    public function getRoutes()
    {
        return $this->routes;
    }



    /**
     * @return Route
    */
    public function getRoute()
    {
        if(! $this->route instanceof Route)
        {
             throw new RuntimeException('No route instantiated!');
        }

        return $this->route;
    }


    /**
     * Get named routes
     *
     * @return array
    */
    public function getNamedRoutes()
    {
        return $this->namedRoutes;
    }


    /**
     * @param Route $route
     * @return Router
    */
    public function add(Route $route)
    {
        $this->routes[] = $this->route = $route;

        return $this;
    }



      /**
       * Set base url
       *
       * @param string $baseUrl
       * @return Router
      */
      public function setBaseUrl(string $baseUrl)
      {
           $this->baseUrl = rtrim($baseUrl, '/');

           return $this;
      }


      /**
       * Set route collection
       *
       * @param array $routes
      */
      public function setRoutes(array $routes)
      {
          foreach ($routes as $route)
          {
              if($route instanceof Route)
              {
                  $this->add($route);
              }

              $this->mapItems($route);
          }
      }



      /**
       * @param array $options
       * @param Closure $callback
      */
      public function group(array $options, Closure $callback)
      {
          $this->options = $options;
          $callback();
          $this->options = [];
      }


     /**
      * @param $prefix
      * @param Closure $callback
     */
     public function prefix($prefix, Closure $callback)
     {
        $this->group(compact('prefix'), $callback);
     }


    /**
     * @param $namespace
     * @param Closure $callback
    */
    public function namespace($namespace, Closure $callback)
    {
        $this->group(compact('namespace'), $callback);
    }



    /**
     * @param string $path
     * @param $target
     * @param string|null $name
     * @return $this
     * @throws RouterException
    */
    public function get(string $path, $target, string $name = null)
    {
        return $this->map(['GET'], $path, $target, $name);
    }


    /**
     * @param string $path
     * @param $target
     * @param string|null $name
     * @return $this
     * @throws RouterException
    */
    public function post(string $path, $target, string $name = null)
    {
        return $this->map(['POST'], $path, $target, $name);
    }


    /**
     * @param string $path
     * @param $target
     * @param string|null $name
     * @return $this
     * @throws RouterException
    */
    public function put(string $path, $target, string $name = null)
    {
        return $this->map(['PUT'], $path, $target, $name);
    }


    /**
     * @param string $path
     * @param $target
     * @param string|null $name
     * @return $this
     * @throws RouterException
    */
    public function delete(string $path, $target, string $name = null)
    {
        return $this->map(['DELETE'], $path, $target, $name);
    }


     /**
      * Map route params
      *
      * @param $methods
      * @param $path
      * @param $target
      * @param string $name
      * @return Router
      */
      public function map($methods, $path, $target, $name = '')
      {
           $route = new Route(
               $this->resolveMethods($methods),
               $this->resolvePath($path),
               $this->resolveTarget($target),
               $this->resolveName($name, $this->resolvePath($path)),
               $this->options
           );

           $route->setMiddleware(
               (array) $this->getOption(self::OPTION_PARAM_MIDDLEWARE)
           );

           return $this->add($route);
      }



     /**
      * @param string $requestMethod
      * @param string $requestUri
      * @return Route|false
     */
     public function match(string $requestMethod, string $requestUri)
     {
          foreach ($this->routes as $route)
          {
             if($route->match($requestMethod, $requestUri))
             {
                return $route;
             }
          }

          return false;
      }



      /**
       * @param array $middleware
       * @return $this
      */
      public function middleware(array $middleware)
      {
           $this->route->setMiddleware($middleware);

           return $this;
      }


       /**
         * @param string $name
         * @return Router
       */
       public function name(string $name)
       {
           $this->route->setName(
               $this->resolveName($name, $this->route->getPath())
           );

           return $this;
       }

    
      /**
        * Set regular expression requirement on the route
        * @param $name
        * @param null $expression
        *
        * @return Router
       */
       public function where($name, $expression = null)
       {
          foreach ($this->parseWhere($name, $expression) as $name => $expression)
          {
              $this->route->setRegex($name, $expression);
          }

          return $this;
       }


    /**
     * Determine parses
     *
     * @param $name
     * @param $expression
     * @return array
    */
    private function parseWhere($name, $expression)
    {
        return \is_array($name) ? $name : [$name => $expression];
    }


    /**
     * @param array $items
     * @return Route
    */
    protected function mapItems(array $items): Route
    {
        $route = new Route();

        foreach ($items as $key => $value)
        {
            $route[$key] = $value;
        }

        $this->add($route);
    }


    /**
     * Get option by given param
     *
     * @param $key
     * @return mixed|null
    */
    private function getOption($key)
    {
        return $this->options[$key] ?? null;
    }



    /**
     * @param $methods
     * @return array
     */
    private function resolveMethods($methods)
    {
        if(is_string($methods))
        {
            $methods = explode('|', $methods);
        }

        return (array) $methods;
    }


    /**
     * @param $path
     * @return string
     */
    private function resolvePath(string $path)
    {
        if($prefix = $this->getOption(self::OPTION_PARAM_PREFIX))
        {
            $path = rtrim($prefix, '/') . '/'. ltrim($path, '/');
        }

        return $path;
    }


    /**
     * @param $target
     * @return string
    */
    private function resolveTarget($target)
    {
        if($namespace = $this->getOption(self::OPTION_PARAM_NAMESPACE))
        {
            $target = rtrim($namespace, '\\') .'\\' . $target;
        }

        return $target;
    }


    /**
     * @param $name
     * @param $path
     * @return mixed
    */
    private function resolveName($name, $path)
    {
        if($name)
        {
            if(isset($this->namedRoutes[$name]))
            {
                throw new RuntimeException(
                    sprintf('This route name (%s) already taken!', $name)
                );
            }

            $this->namedRoutes[$name] = $path;
        }

        return $name;
    }
}