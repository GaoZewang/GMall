<?php
/**
 * @Project   Gmall
 * @File      SystemCategoryValidate.php
 * @Author    MrGao
 * @Date      2025/12/4 15:16
 */

namespace app\admin\validate;

use Respect\Validation\Validator;

class SystemCategoryValidate
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
                'category_name' => Validator::stringType()->notEmpty()->setName('category_name'),
                'category_level' => Validator::notEmpty()->setName('category_level'),
            ],
            'edit'=>[
                'id' => Validator::intVal()->positive()->setName('ID'),
                'category_name' => Validator::stringType()->notEmpty()->setName('category_name'),
                'category_level' => Validator::notEmpty()->setName('category_level'),
            ],
            'status'=>[
                'id' => Validator::intVal()->positive()->setName('ID'),
                'category_status' => Validator::intVal()->in([0,1])->setName('Status'),
            ],
        ];
        Validator::input($data, $scenes[$scene]);
    }
}