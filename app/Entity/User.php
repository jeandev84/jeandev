<?php
namespace App\Entity;


/**
 * Class User
 * @package App\Entity
*/
class User
{

    const ROLES = [
       'ROLE_ADMIN' => 'админ'
    ];


     /**
      * User constructor.
     */
     public function __construct()
     {
          echo 'User::class';
     }


     /**
      * @return string
     */
     public function getRole()
     {
         return self::ROLES['ROLE_ADMIN'] ?? '';
     }


     /**
      * @return string[]
     */
     public function getRoles()
     {
         return self::ROLES;
     }
}