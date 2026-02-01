<?php
/**
 * @Project   Gmall
 * @File      UsersValidate.php
 * @Author    MrGao
 * @Date      2026/02/01 08:33
 */

namespace app\admin\validate;

use Respect\Validation\Validator;

class UsersValidate
{
    /**
     * @param $data
     * @param $scene
     * @return string|void
     */
    public static function validate($data,$scene)
    {
        $scenes=[
            'list'=>[
                'page'      => Validator::intVal()->min(1)->setName('页码'),
                'per_page' => Validator::intVal()->positive()->setName('每页条数'),
            ],
            'info'=>[
                'id' => Validator::intVal()->positive()->setName('数据主键'),
            ],
            'add'=>[
                'name' => \Respect\Validation\Validator::notEmpty()->stringType()->length(null, 255)->setName('名称'),
                'email' => \Respect\Validation\Validator::stringType()->length(null, 255)->setName('邮箱'),
                'status' => \Respect\Validation\Validator::notEmpty()->intVal()->positive()->setName('状态'),
                'created_at' => \Respect\Validation\Validator::date()->setName('创建时间'),
            ],
            'edit'=>[
                'id' => Validator::intVal()->positive()->notEmpty()->setName('ID'),
                'name' => \Respect\Validation\Validator::notEmpty()->stringType()->length(null, 255)->setName('名称'),
                'email' => \Respect\Validation\Validator::stringType()->length(null, 255)->setName('邮箱'),
                'status' => \Respect\Validation\Validator::notEmpty()->intVal()->positive()->setName('状态'),
                'created_at' => \Respect\Validation\Validator::date()->setName('创建时间'),
            ],
            'del'=>[
                'id' => Validator::intVal()->positive()->setName('ID'),
            ],
            'status'=>[
                'id' => Validator::intVal()->positive()->setName('ID'),
                'status' => Validator::intVal()->in([0,1])->setName('Status'),
            ],
        ];
        Validator::input($data, $scenes[$scene]);
    }
}
