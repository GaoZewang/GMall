<?php

namespace app\admin\validate;

use Respect\Validation\Exceptions\ValidationException;
use Respect\Validation\Validator;
use support\Db;

class SystemPermissionValidate
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
                'name' => Validator::notEmpty()->stringType()->setName('权限名称'),
                'code' => Validator::notEmpty()->stringType()->setName('权限代码'),
                'route_url' => Validator::notEmpty()->stringType()->setName('权限路由'),
                'description' => Validator::notEmpty()->stringType()->setName('权限名描述'),
            ],
            'edit'=>[
                'id' => Validator::intVal()->positive()->setName('数据主键'),
                'name' => Validator::notEmpty()->stringType()->setName('权限名称'),
                'code' => Validator::notEmpty()->stringType()->setName('权限代码'),
                'route_url' => Validator::notEmpty()->stringType()->setName('权限路由'),
                'description' => Validator::notEmpty()->stringType()->setName('权限名描述'),
            ],
        ];
        Validator::input($data, $scenes[$scene]);
    }
}