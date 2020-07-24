<?php
namespace Jan\Component\Routing;


use Closure;
use RuntimeException;



/**
 * Class Router
 * @package Jan\Component\Routing
*/
class Router
{

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
        * Set pattern
        *
        * @var array
      */
      protected $patterns = [];



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
       * Set base url
       *
       * @param string $baseUrl
       * @return Router
      */
      public function setBaseUrl(string $baseUrl)
      {
           $this->baseUrl = trim($baseUrl, '/');

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
            $route = new Route();
            $route->setMethods($this->resolveMethods($methods));
            $route->setPath($this->resolvePath($path));
            $route->setTarget($this->resolveTarget($target));
            $route->setName($name);
            $route->setOptions($this->options);

            $this->add($route);


            return $this;
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
           $this->route->setName($name);

           return $this;
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
          return $this->route;
      }


     /**
      * @param Route $route
      * @return Router
     */
     protected function add(Route $route)
     {
        $this->routes[] = $this->route = $route;

        return $this;
     }



    /**
     * @param $methods
     * @return array
    */
    protected function resolveMethods($methods)
    {
        if(is_string($methods))
        {
            $methods = explode('|', $methods);
        }

        return (array) $methods;
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
            $this->patterns[$name] = $expression;
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
     * @param $path
     * @return string
    */
    private function resolvePath($path)
    {
        if($prefix = $this->getOption('prefix'))
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
        if($namespace = $this->getOption('namespace'))
        {
            $target = rtrim($namespace, '\\') .'\\' . $target;
        }

        return $target;
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
}