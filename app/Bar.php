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
          echo __METHOD__.'<br>';
      }


      public function index($id)
      {
           echo $id;
      }
}