<?php
namespace app\api\validate;
use Respect\Validation\Validator as v;

class GoodsValidate
{
     /**
         * @param $data
         * @param $scene
         * @return string|void
         */
        public static function validate($data,$scene)
        {
            $scenes = [
                'add' => [
                    'goods_code'     => v::stringType()->length(0,255),
                    'merchant_id'    => v::intVal()->notEmpty(),
                    'shop_id'        => v::intVal()->notEmpty(),
                    'goods_name'     => v::stringType()->length(0,255)->notEmpty(),
                    'subtitle'       => v::stringType()->length(0,255),
                    'category_id'    => v::intVal()->notEmpty(),
                    'cover_image'    => v::stringType()->length(0,255),
                    'images'         => v::alwaysValid(),
                    'description'    => v::stringType()->length(0,255),
                    'attrs_template' => v::alwaysValid(),
                    'goods_status'   => v::intVal()->notEmpty(),
                    'is_deleted'     => v::intVal()->notEmpty(),
                    'created_at'     => v::date()->notEmpty(),
                    'updated_at'     => v::date()->notEmpty(),
                ],
                'edit' => [
                   'id'             => v::intVal()->positive(),
                   'goods_code'     => v::optional(v::stringType()->length(0,255)),
                   'merchant_id'    => v::optional(v::intVal()->notEmpty()),
                   'shop_id'        => v::optional(v::intVal()->notEmpty()),
                   'goods_name'     => v::optional(v::stringType()->length(0,255)->notEmpty()),
                   'subtitle'       => v::optional(v::stringType()->length(0,255)),
                   'category_id'    => v::optional(v::intVal()->notEmpty()),
                   'cover_image'    => v::optional(v::stringType()->length(0,255)),
                   'images'         => v::optional(v::alwaysValid()),
                   'description'    => v::optional(v::stringType()->length(0,255)),
                   'attrs_template' => v::optional(v::alwaysValid()),
                   'goods_status'   => v::optional(v::intVal()->notEmpty()),
                   'is_deleted'     => v::optional(v::intVal()->notEmpty()),
                   'created_at'     => v::optional(v::date()->notEmpty()),
                   'updated_at'     => v::optional(v::date()->notEmpty()),
                ],
                'list' => [
                   'goods_code'     => v::optional(v::stringType()->length(0,255)),
                   'merchant_id'    => v::optional(v::intVal()->notEmpty()),
                   'shop_id'        => v::optional(v::intVal()->notEmpty()),
                   'goods_name'     => v::optional(v::stringType()->length(0,255)->notEmpty()),
                   'subtitle'       => v::optional(v::stringType()->length(0,255)),
                   'category_id'    => v::optional(v::intVal()->notEmpty()),
                   'cover_image'    => v::optional(v::stringType()->length(0,255)),
                   'images'         => v::optional(v::alwaysValid()),
                   'description'    => v::optional(v::stringType()->length(0,255)),
                   'attrs_template' => v::optional(v::alwaysValid()),
                   'goods_status'   => v::optional(v::intVal()->notEmpty()),
                   'is_deleted'     => v::optional(v::intVal()->notEmpty()),
                   'created_at'     => v::optional(v::date()->notEmpty()),
                   'updated_at'     => v::optional(v::date()->notEmpty()),
                ],
                'info' => [
                   'id'             => v::intVal()->positive(),
                ],
                'del' => [
                   'id'             => v::intVal()->positive(),
                ],
            ];
            v::input($data, $scenes[$scene]);
        }
}
