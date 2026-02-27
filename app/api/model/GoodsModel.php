<?php
/**
 * @Project   Gmall
 * @File      model_template.php
 * @Author    MrGao
 * @Date      2026/2/1 18:18
 */

namespace app\api\model;

use app\model\BaseModel;

class GoodsModel extends BaseModel
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = "goods";



    /**
     * 重定义主键，默认是id
     *
     * @var string
     */
    protected $primaryKey = 'id';

    /**
     * 指示是否自动维护时间戳
     *
     * @var bool
     */
    public $timestamps = true;

    /**
     * 可批量赋值的字段
     *
     * @var array
     */
    protected $fillable = [
        'id',
        'goods_code',
        'merchant_id',
        'shop_id',
        'goods_name',
        'subtitle',
        'category_id',
        'cover_image',
        'images',
        'description',
        'attrs_template',
        'goods_status',
        'is_deleted',
        'created_at',
        'updated_at'
    ];
}
