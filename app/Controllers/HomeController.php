<?php
namespace App\Controllers;


use Jan\Component\DI\Contracts\ContainerInterface;


/**
 * Class HomeController
 * @package App\Controllers
*/
class HomeController
{

   /** @var ContainerInterface  */
   protected $container;


   /**
     * HomeController constructor.
     * @param ContainerInterface $container
   */
   public function __construct(ContainerInterface $container)
   {
        $this->container = $container;
   }

   public function index(ContainerInterface $container, $slug, $id)
   {
       // dump($this->container);
       // dump($slug, $id);
       dump(__METHOD__);
   }


   public function about()
   {
        dump(__METHOD__);
   }


   public function contact()
   {
        dump(__METHOD__);
   }
}