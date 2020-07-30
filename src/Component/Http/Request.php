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
        // TODO: Implement getQueryParams() method.
    }

    public function getMethod()
    {
        // TODO: Implement getMethod() method.
    }

    public function getUri()
    {
        // TODO: Implement getUri() method.
    }

    public function getParsedBody()
    {
        // TODO: Implement getParsedBody() method.
    }
}