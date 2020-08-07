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
      * @return static|false
     */
     public static function load(string $root)
     {
         if (! is_dir($root))
         {
             return false;
         }

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
         $this->namespaceMap[$namespace] = trim($rootDir, '\\/');
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
                 require_once $this->generateFilename($namespace, $pathParts);

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


    /**
     * @param $namespace
     * @param $pathParts
     * @return string
     */
     protected function generateFilename($namespace, $pathParts)
     {
         return static::$root . '/' . $this->namespaceMap[$namespace] . '/' . implode('/', $pathParts) . '.php';
     }
}
