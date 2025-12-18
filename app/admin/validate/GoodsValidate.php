<?php
/**
 * @Project   Gmall
 * @File      GoodsValidate.php
 * @Author    MrGao
 * @Date      2025/12/18 14:37
 */

namespace app\admin\validate;

use Respect\Validation\Validator;

class GoodsValidate
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
                'merchant_id' => Validator::stringType()->notEmpty()->setName('商户ID'),
                'goods_name' => Validator::notEmpty()->setName('商品名称'),
                'subtitle' => Validator::notEmpty()->setName('副标题'),
                'category_id' => Validator::intVal()->positive()->setName('类目ID'),
                'cover_image' => Validator::notEmpty()->setName('主图'),
                'images' => Validator::notEmpty()->setName('轮播图'),
                'description' => Validator::notEmpty()->setName('图文详情'),
                'attrs_template' => Validator::notEmpty()->setName('属性模板（规格名/属性名等）'),
                'sku_list' => Validator::notEmpty()->setName('商品sku'),
            ],
            'edit'=>[
                'id' => Validator::intVal()->positive()->setName('ID'),
                'merchant_id' => Validator::stringType()->notEmpty()->setName('商户ID'),
                'goods_name' => Validator::notEmpty()->setName('商品名称'),
                'subtitle' => Validator::notEmpty()->setName('副标题'),
                'category_id' => Validator::notEmpty()->setName('类目ID'),
                'cover_image' => Validator::notEmpty()->setName('主图'),
                'images' => Validator::notEmpty()->setName('轮播图'),
                'description' => Validator::notEmpty()->setName('图文详情'),
                'attrs_template' => Validator::notEmpty()->setName('属性模板（规格名/属性名等）'),
                'sku_list' => Validator::notEmpty()->setName('商品sku'),
            ],
            'status'=>[
                'id' => Validator::intVal()->positive()->setName('ID'),
                'goods_status' => Validator::intVal()->in([0,1])->setName('Status'),
            ],
        ];
        Validator::input($data, $scenes[$scene]);
    }
}