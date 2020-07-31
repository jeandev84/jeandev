<?php
namespace Jan\Component\Http;


use Jan\Component\Http\Contracts\ResponseInterface;

/**
 * Class Response
 * @package Jan\Component\Http
*/
class Response implements ResponseInterface
{


     /**
      * @var int
     */
     private $status;


     /**
      * @var string $content
     */
     private $content;


     /**
      * @var array
     */
     private $headers = [];


     /**
      * Response constructor.
      * @param null $content
      * @param int $status
      * @param array $headers
     */
     public function __construct($content = null, int $status = 200, array $headers = [])
     {
         $this->setContent($content);
         $this->setStatus($status);
         $this->setHeaders($headers);
     }



     /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }



    /**
     * @param int $status
    */
    public function setStatus(int $status)
    {
        $this->status = $status;
    }


     /**
      * @return string
     */
     public function getContent(): string
     {
          return $this->content;
     }


     /**
      * @param $content
      * @return void
     */
     public function setContent($content)
     {
          $this->content = $content;
     }



     /**
      * @return array
     */
     public function getHeaders(): array
     {
         return $this->headers;
     }


     /**
      * @param array $headers
      * @return void
     */
     public function setHeaders(array $headers)
     {
          $this->headers = $headers;
     }


     /**
      * @param $name
      * @param null $value
      * @return Response
     */
     public function withHeader($name, $value = null)
     {
          return $this;
     }



     /**
      * @param $body
      * @return $this
     */
     public function withBody($body)
     {
         $this->setContent($body);
         return $this;
     }


     /**
      * @param array $data
      * @return $this
     */
     public function withJson(array $data)
     {
         // add header type json
         return $this->withBody(\json_encode($data));
     }


     /**
      * Send response
     */
     public function send()
     {

     }

    public function withStatus($status)
    {
        // TODO: Implement withStatus() method.
    }
}