<?php
namespace Jan\Component\Routing;


/**
 * Class Router
 * @package Jan\Component\Routing
*/
class Router
{

      /** @var Route */
      protected $route;


      /** @var array  */
      protected $routes = [];


      public function __construct()
      {
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
       * @param $methods
       * @param $path
       * @param $target
       * @param null $name
       * @return Router
      */
      public function map($methods, $path, $target, $name = null)
      {
            $route = new Route();

            $route->setMethods($this->resolveMethods($methods));
            $route->setPath($path);
            $route->setTarget($target);
            $route->setName($name);

            return $this->add($route);
      }


     /**
      * @param $methods
      * @return array
     */
      public function resolveMethods($methods)
      {
          if(is_string($methods))
          {
              $methods = explode('|', $methods);
          }

          return (array) $methods;
      }


      /**
       * @param string $requestMethod
       * @param string $requestUri
       * @return bool
      */
      public function match(string $requestMethod, string $requestUri)
      {
           foreach ($this->routes as $route)
           {
                if($route->match($requestMethod, $requestUri))
                {
                    dump($route);

                    return true;
                }
           }

           return false;
      }


      /**
        * @return array
      */
      public function routes()
      {
          return $this->routes;
      }
}