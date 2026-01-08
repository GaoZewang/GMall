<?php

namespace app\admin\validate;

use Respect\Validation\Validator;

class SystemSettingValidate
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
               'tag' => Validator::stringType()->notEmpty()->length(1, 20)->setName('设置标识'),
               'set_name' => Validator::stringType()->notEmpty()->length(1, 50)->setName('设置名'),
               'set_content' => Validator::stringType()->notEmpty()->setName('设置内容'),
           ],
           'edit'=>[
               'id' => Validator::intVal()->positive()->setName('ID'),
               'set_tag' => Validator::stringType()->notEmpty()->length(1, 20)->setName('设置标识'),
               'set_name' => Validator::stringType()->notEmpty()->length(1, 50)->setName('设置名'),
               'set_content' => Validator::stringType()->notEmpty()->setName('设置内容'),
           ],
           'info'=>[
               'id' => Validator::intVal()->positive()->setName('ID'),
           ],
           'delete'=>[
               'id' => Validator::intVal()->positive()->setName('ID'),
           ],
       ];
       Validator::input($data, $scenes[$scene]);
   }
}
