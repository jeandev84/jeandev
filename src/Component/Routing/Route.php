<?php
namespace Jan\Component\Routing;


/**
 * Class Route
 * @package Jan\Component\Routing
*/
class Route implements \ArrayAccess
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
      * @var array
     */
     private $regex = [];



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
     private $middleware = [];



     /**
      * @var array
     */
     private $options = [];



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
     * @return string
     */
    public function getPattern()
    {
        return '#^' . $this->path . '$#';
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
     * @return array
    */
    public function getRegex(): array
    {
        return $this->regex;
    }



    /**
     * @param $name
     * @param $expression
     * @return Route
    */
    public function setRegex($name, $expression): Route
    {
        $this->regex[$name] = $expression;

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
     * @return array
     */
    public function getMiddleware(): array
    {
        return $this->middleware[$this->path] ?? [];
    }



    /**
     * @param array $middleware
     * @return Route
    */
    public function setMiddleware(array $middleware): Route
    {
        $this->middleware = $middleware;
        return $this;
    }



    /**
     * @return array
     */
    public function getOptions(): array
    {
        return $this->options;
    }



    /**
     * @param array $options
     * @return Route
    */
    public function setOptions(array $options): Route
    {
        $this->options = $options;
        return $this;
    }


    /**
     * @param $index
     * @return mixed|null
    */
    public function getOption($index)
    {
        return $this->options[$index] ?? null;
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
        return $this->isMatchingMethod($requestMethod)
               && $this->isMatchingPath($requestUri);
    }


    /**
     * @param $name
     * @param $value
    */
    public function set($name, $value)
    {
        if($this->has($name))
        {
             $this->{$name} = $value;
        }
    }



    /**
     * @param $name
     * @return bool
    */
    public function has($name)
    {
        return property_exists($this, $name);
    }


    /**
     * @param $name
     * @return
    */
    public function get($name)
    {
         if($this->has($name))
         {
             return $this->{$name};
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
     * @return mixed|void
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
        $this->set($offset, $value);
    }


    /**
     * @param mixed $offset
    */
    public function offsetUnset($offset)
    {
        if($this->has($offset))
        {
            unset($this->{$offset});
        }
    }
}