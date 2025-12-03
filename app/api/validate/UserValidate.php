<?php
namespace app\api\validate;
use Respect\Validation\Validator;

/**
 * @Project   Gmall
 * @File      UserValidate.php
 * @Author    MrGao
 * @Date      2025/12/3 15:20
 */
class UserValidate
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
                'nickname' => Validator::notEmpty()->stringType()->setName('nickname'),
                'username' => Validator::notEmpty()->stringType()->setName('username'),
                'email' => Validator::notEmpty()->stringType()->setName('email'),
                'password' => Validator::notEmpty()->stringType()->setName('password'),
                'phone' => Validator::notEmpty()->stringType()->setName('phone'),
                'register_channel' => Validator::notEmpty()->stringType()->setName('register_channel'),
            ],
            'edit'=>[
                'id' => Validator::notEmpty()->intVal()->positive()->setName('ID'),
                'nickname' => Validator::notEmpty()->stringType()->setName('nickname'),
                'username' => Validator::notEmpty()->stringType()->setName('username'),
                'email' => Validator::notEmpty()->stringType()->setName('email'),
                'password' => Validator::notEmpty()->stringType()->setName('password'),
                'phone' => Validator::notEmpty()->stringType()->setName('phone'),
                'register_channel' => Validator::notEmpty()->stringType()->setName('register_channel'),
            ],
            'status'=>[
                'id' => Validator::intVal()->positive()->setName('ID'),
                'status' => Validator::intVal()->in([0,1])->setName('Status'),
            ],
        ];
        Validator::input($data, $scenes[$scene]);
    }
}