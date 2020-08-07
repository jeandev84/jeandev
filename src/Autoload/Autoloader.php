<?php
namespace Jan\Autoload;


/**
 * Class Autoloader
 * @package Jan\Autoload
*/
class Autoloader
{

     /**
      * @var string
     */
     protected static $root;


     /**
      * @var array
     */
     protected $namespaceMap = [];


     /**
      * @param string $root
      * @return Autoloader
     */
     public static function load(string $root)
     {
          static::$root = $root;

          return new static();
     }


    /**
      * @param $namespace
      * @param $rootDir
      * @return Autoloader
     */
     public function addNamespace($namespace, $rootDir)
     {
         if (is_dir($rootDir))
         {
             $this->namespaceMap[$namespace] = rtrim($rootDir, '\\/');
         }

         return $this;
     }


     /**
      * Autoload register
     */
     public function register()
     {
          /* spl_autoload_register(static::class .'::autoload'); */
          spl_autoload_register([$this, 'autoload']);
     }


     /**
      * @param $classname
      * @return bool
     */
     protected function autoload($classname)
     {
         $pathParts = explode('\\', $classname);

         if(is_array($pathParts))
         {
             $namespace = array_shift($pathParts) .'\\';

             if(! empty($this->namespaceMap[$namespace]))
             {
                 $filePath = $this->namespaceMap[$namespace] . '/' . implode('/', $pathParts) . '.php';

                 require_once $filePath;

                 return true;
             }
         }

         return false;
     }


     /**
       * @return array
     */
     public function getMappedNamespaces()
     {
         return $this->namespaceMap;
     }
}
