<?php

namespace app\admin\model;

use support\Model;

class StoreModel extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'admin_store';

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
        'merchant_id',
        'admin_user_id',
        'balance',
        'revenue',
        'name',
        'address',
        'contact_phone',
        'status'
    ];

    /**
     * 日期格式转换
     *
     * @var array
     */
    protected $casts = [
        'balance' => 'decimal:2',
        'revenue' => 'decimal:2',
//        'status' => 'boolean'
    ];

    /**
     * 关联商户
     */
    public function merchant()
    {
        return $this->belongsTo(MerchantModel::class, 'merchant_id', 'id');
    }

    /**
     * 获取单个店铺信息
     * @param array       $where 查询条件
     * @param array|mixed $field 查询字段
     * @return array
     */
    public function getStoreInfo(array $where, $field = ['*']): array
    {
        return $this->where($where)->first($field)?->toArray() ?? [];
    }

    /**
     * 添加店铺
     * @param array $data 插入的数据
     * @return bool
     */
    public function addStore(array $data): bool
    {
        return $this->insert($data);
    }

    /**
     * 编辑店铺
     * @param array $where 条件
     * @param array $data  更新数据
     * @return bool
     */
    public function editStore(array $where, array $data): bool
    {
        return $this->where($where)->update($data);
    }
}