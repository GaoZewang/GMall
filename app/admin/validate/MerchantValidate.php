<?php
/**
 * @Project   Gmall
 * @File      MerchantValidate.php
 * @Author    MrGao
 * @Date      2025/12/18 14:42
 */

namespace app\admin\validate;

use Respect\Validation\Validator;

class MerchantValidate
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
                'admin_user_id' => Validator::stringType()->notEmpty()->setName('管理员用户ID'),
                'name' => Validator::notEmpty()->setName('商户名称'),
                'logo' => Validator::notEmpty()->setName('商户logo'),
                'address' => Validator::intVal()->positive()->setName('商户地址'),
                'contact_phone' => Validator::notEmpty()->setName('商户联系电话'),
            ],
            'edit'=>[
                'id' => Validator::intVal()->positive()->setName('ID'),
                'admin_user_id' => Validator::stringType()->notEmpty()->setName('管理员用户ID'),
                'name' => Validator::notEmpty()->setName('商户名称'),
                'logo' => Validator::notEmpty()->setName('商户logo'),
                'address' => Validator::intVal()->positive()->setName('商户地址'),
                'contact_phone' => Validator::notEmpty()->setName('商户联系电话'),
            ],
            'status'=>[
                'id' => Validator::intVal()->positive()->setName('ID'),
                'status' => Validator::intVal()->in([0,1])->setName('Status'),
            ],
        ];
        Validator::input($data, $scenes[$scene]);
    }
}