<?php

namespace app\validate;

use Respect\Validation\Validator as v;
use support\Db;

class BaseValidate
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
                'page'      => v::intVal()->min(1)->setName('页码'),
                'page_size' => v::intVal()->positive()->setName('每页条数'),
            ],
            'info'=>[
                'id' => v::intVal()->positive()->setName('数据主键'),
            ],
        ];
        v::input($data, $scenes[$scene]);
    }
}