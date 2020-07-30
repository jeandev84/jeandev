<?php
namespace Jan\Foundation;


use Closure;
use Exception;
use Jan\Component\DI\Container;
use Jan\Component\DI\Exceptions\ContainerException;
use Jan\Component\DI\Exceptions\ResolverDependencyException;
use Jan\Component\Http\Response;
use Jan\Component\Http\Contracts\RequestInterface;
use Jan\Component\Http\Contracts\ResponseInterface;
use Jan\Component\Routing\Router;
use ReflectionException;


/**
 * Class Application
 * @package Jan\Foundation
*/
class Application extends Container
{

     public function __construct()
     {
         $this->runBindings();
     }


     public function runBindings()
     {
         $this->singleton(Router::class, Router::class);
     }


     /**
      * @param RequestInterface $request
      * @return mixed
      * @throws ContainerException
      * @throws ResolverDependencyException
      * @throws ReflectionException
      * @throws Exception
     */
     public function run(RequestInterface $request)
     {
         $router = $this->get(Router::class);

         if(! $router->getRoutes())
         {
              // call default action
             return $this->call('Jan\Foundation\DefaultController', [], 'index');
         }

         $route = $router->match($request->getMethod(), $request->getUri());

         if(! $route)
         {
              /* exit('404 page not found!'); */
              throw new Exception('Page not found!', 404);
         }

         $this->bind('_routeParams', (array) $route);

         $target = $route->getTarget();

         if(! $target instanceof Closure)
         {
             if(is_string($target))
             {
                 list($controller, $method) = explode('@', $target);

                 return $this->call(
                     sprintf('App\Controllers\%s', $controller),
                     $route->getMatches(),
                     $method
                 );
             }
         }

         return $this->call($target, $route->getMatches());
     }


     /**
      * @param $target
      * @return Closure|false|string[]
     */
     public function resolveRouteTarget($target)
     {
         if($target instanceof Closure)
         {
              return $target;
         }


         if(is_string($target))
         {
             return explode('@', $target);
         }


         /*
         TO DISCUSS
         return str_replace(['@', '\\'], DIRECTORY_SEPARATOR, $target);
         */
     }


     /**
      * @param $respond
      * @return ResponseInterface
     */
     public function response($respond)
     {
          if(! $respond instanceof ResponseInterface)
          {
               $response = new Response();

               if(is_array($respond))
               {
                    return $response->withJson($respond);
               }

               return $response->withBody($respond);
          }

          return $respond;
     }
}