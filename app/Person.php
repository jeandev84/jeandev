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
          $this->setName($name);
          $this->setEmail($email);
     }



     /**
      * @return null|string
     */
     public function getName(): ?string
     {
        return $this->name;
     }



     /**
      * @param null|string $name
      * @return Person
     */
     public function setName(?string $name): Person
     {
        $this->name = $name;

        return $this;
     }



     /**
      * @return null|string
     */
     public function getEmail(): ?string
     {
        return $this->email;
     }


     /**
      * @param string|null $email
      * @return Person
     */
     public function setEmail(?string $email): Person
     {
        $this->email = $email;
        return $this;
     }

}