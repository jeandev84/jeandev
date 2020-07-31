<?php
namespace Jan\Component\Http;


use Jan\Component\Http\Contracts\RequestInterface;

/**
 * Class Request
 * @package Jan\Component\Http
*/
class Request implements RequestInterface
{

    public function __construct()
    {
    }


    public function getQueryParams()
    {
        return $_GET;
    }

    public function getMethod()
    {
         return $_SERVER['REQUEST_METHOD'];
    }

    public function getUri()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function getParsedBody()
    {
        return 'Content';
    }
}