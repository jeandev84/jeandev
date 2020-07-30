<?php
namespace Jan\Component\Http\Contracts;


/**
 * Interface RequestInterface
 * @package Jan\Component\Http\Contracts
*/
interface RequestInterface
{
     public function getQueryParams();
     public function getMethod();
     public function getUri();
     public function getParsedBody();
}