<?php
namespace Jan\Component\DI;


use Jan\Component\DI\Contracts\ContainerInterface;

/**
 * Class Container
 * @package Jan\Component\DI
*/
class Container implements ContainerInterface
{

    /**
     * @var array
    */
    protected $bindings = [];


    /**
     * @param $abstract
     * @param null $concrete
     * @param bool $shared
    */
    public function bind($abstract, $concrete = null, $shared = false)
    {
          if(! $concrete)
          {
              $concrete = $abstract;
          }

          $this->bindings[$abstract] = compact('concrete', 'shared');
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
     * @return array
    */
    public function getBindings()
    {
        return $this->bindings;
    }


    /**
     * @param $id
     * @param array $params
     * @return mixed|void
    */
    public function get($id, $params = [])
    {

    }


    /**
     * @param $id
     * @return bool
    */
    public function has($id)
    {
        if(isset($this->bindings[$id]))
        {
            return true;
        }



        return false;
    }
}