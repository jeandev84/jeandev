<?php
namespace Jan\Component\Routing\Contracts;


/**
 * Interface RouterInterface
 * @package Jan\Component\Routing\Contract
*/
interface RouterInterface
{

    /**
     * Set Routes
     *
     * @param array $routes
     * @return mixed
     */
     public function setRoutes(array $routes);



     /**
      * Get all routes
      *
      * @return array
     */
     public function getRoutes();



     /**
      * Determine if current route path match URI
      *
      * @param string $requestMethod
      * @param string $requestUri
      * @return mixed
     */
     public function match(string $requestMethod, string $requestUri);



     /**
      * Generate URI
      *
      * @param $name
      * @param array $params
      * @return mixed
     */
     public function generate(string $name, array $params = []);
}