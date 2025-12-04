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
                'parent_id' => Validator::stringType()->notEmpty()->setName('parent_id'),
                'category_level' => Validator::stringType()->notEmpty()->setName('category_level'),
                'is_leaf' => Validator::stringType()->notEmpty()->setName('is_leaf'),
            ],
            'edit'=>[
                'id' => Validator::intVal()->positive()->setName('ID'),
                'category_name' => Validator::stringType()->notEmpty()->setName('category_name'),
                'parent_id' => Validator::stringType()->notEmpty()->setName('parent_id'),
                'category_level' => Validator::stringType()->notEmpty()->setName('category_level'),
                'is_leaf' => Validator::stringType()->notEmpty()->setName('is_leaf'),
            ],
            'status'=>[
                'id' => Validator::intVal()->positive()->setName('ID'),
                'status' => Validator::intVal()->in([0,1])->setName('Status'),
            ],
        ];
        Validator::input($data, $scenes[$scene]);
    }
}