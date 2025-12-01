<?php

namespace app\admin\validate;

use Respect\Validation\Validator;

class SystemRoleValidate
{
    /**
     * @param $data
     * @param $scene
     * @return string|void
     */
   public static function validate($data,$scene)
   {
       $scenes=[
           'add'=>[
               'name' => Validator::stringType()->notEmpty()->setName('Name'),
               'slug' => Validator::stringType()->notEmpty()->setName('slug'),
               'description' => Validator::stringType()->notEmpty()->setName('Description'),
           ],
           'edit'=>[
               'id' => Validator::intVal()->positive()->setName('ID'),
               'name' => Validator::stringType()->notEmpty()->setName('Name'),
               'slug' => Validator::stringType()->notEmpty()->setName('slug'),
               'description' => Validator::stringType()->notEmpty()->setName('Description'),
           ],
           'status'=>[
               'id' => Validator::intVal()->positive()->setName('ID'),
               'status' => Validator::intVal()->in([0,1])->setName('Status'),
           ],
       ];
       Validator::input($data, $scenes[$scene]);
   }
}