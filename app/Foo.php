<?php
namespace App;


/**
 * Class Foo
 * @package App
*/
class Foo
{

      /**
       * Foo constructor.
       * @param Bar $bar
      */
      public function __construct(Bar $bar)
      {
           echo 'Foo::class';
      }
}