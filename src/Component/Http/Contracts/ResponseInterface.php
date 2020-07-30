<?php
namespace Jan\Component\Http\Contracts;


/**
 * Interface ResponseInterface
 * @package Jan\Component\Http\Contracts
*/
interface ResponseInterface
{
    /**
     * @param $status
     * @return mixed
    */
    public function withStatus($status);


    /**
     * @param $body
     * @return mixed
    */
    public function withBody($body);


    /**
     * @param $headers
     * @return mixed
    */
    public function withHeader($headers);



    /**
     * Send response
     * @return mixed
    */
    public function send();
}