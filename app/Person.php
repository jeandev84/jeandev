<?php
namespace App;


/**
 * Class Person
 * @package App
 */
class Person
{

    /**
     * @var string
    */
    private $name;


    /**
     * @var string
    */
    private $email;


    /**
      * Person constructor.
      * @param $name
      * @param $email
     */
     public function __construct($name = null, $email = null)
     {

     }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return Person
     */
    public function setName(string $name, string $email): Person
    {
        $this->name = $name;
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     * @return Person
     */
    public function setEmail(string $email): Person
    {
        $this->email = $email;
        return $this;
    }

}