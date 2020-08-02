<?php
namespace App;


/**
 * Class Bar
 * @package App
*/
class Bar
{
      public function __construct()
      {
          echo 'Bar::class';
      }


      public function index($id)
      {
           echo $id;
      }
}