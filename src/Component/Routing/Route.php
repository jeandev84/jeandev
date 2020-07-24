<?php
namespace Jan\Component\Routing;


/**
 * Class Route
 * @package Jan\Component\Routing
*/
class Route
{

     /**
      * @var array
     */
     private $methods = [];


     /**
      * @var string
     */
     private $path;


     /**
      * @var mixed
     */
     private $target;


     /**
      * @var string
     */
     private $name;



     /**
      * @var array
     */
     private $matches = [];


     /**
      * @var array
     */
     private $namedRoutes = [];


     /**
      * @var array
     */
     private $middleware = [];



    /**
     * @return array
    */
    public function getMethods(): array
    {
        return $this->methods;
    }



    /**
     * @param array $methods
     * @return Route
    */
    public function setMethods(array $methods): Route
    {
        $this->methods = $methods;
        return $this;
    }



    /**
     * @return string
    */
    public function getPath(): string
    {
        return $this->path;
    }


    /**
     * @param string $path
     * @return Route
    */
    public function setPath(string $path): Route
    {
        $this->path = $path;
        return $this;
    }


    /**
     * @return mixed
    */
    public function getTarget()
    {
        return $this->target;
    }


    /**
     * @param mixed $target
     * @return Route
    */
    public function setTarget($target)
    {
        $this->target = $target;
        return $this;
    }



    /**
     * @return string
    */
    public function getName(): string
    {
        return $this->name;
    }



    /**
     * @param string $name
     * @return Route
    */
    public function setName(string $name): Route
    {
        $this->name = $name;

        $this->namedRoutes[$name] = $this->path;

        return $this;
    }



    /**
     * @return array
     */
    public function getMatches(): array
    {
        return $this->matches;
    }

    /**
     * @param array $matches
     * @return Route
     */
    public function setMatches(array $matches): Route
    {
        $this->matches = $matches;
        return $this;
    }


    /**
     * @return string
    */
    public function getPattern()
    {
        return '#^' . $this->path . '$#';
    }

    /**
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }


    /**
     * @param string $middleware
     * @return Route
    */
    public function setMiddleware(string $middleware): Route
    {
        $this->middleware[$this->path] = $middleware;
        return $this;
    }




    /**
     * @param string $requestMethod
     * @return bool
    */
    public function isMatchingMethod(string $requestMethod)
    {
         return \in_array($requestMethod, $this->methods);
    }


    /**
     * @param string $requestUri
     * @return bool
    */
    public function isMatchingPath(string $requestUri)
    {
        $matches = [];

        if(preg_match($this->getPattern(), $requestUri, $matches))
        {
            $this->setMatches($matches);

            return true;
        }

        return false;
    }


    /**
     * @param $requestMethod
     * @param $requestUri
     * @return bool
    */
    public function match($requestMethod, $requestUri)
    {
        return $this->isMatchingMethod($requestMethod) && $this->isMatchingPath($requestUri);
    }
}