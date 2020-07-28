<?php
namespace App\Http;


/**
 * Class Request
 * @package App\Http
*/
class Request implements RequestInterface
{
     public function __construct()
     {
          echo __METHOD__;
     }
}