<?php

namespace app\admin\model;

use support\Model;

class SystemRoleModel extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'system_role';

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
    public $timestamps = false;

    /**
     * @param $where
     * @param $filed
     * @param $orderFiled
     * @param $order
     * @param $page
     * @param $perPage
     * @return mixed[]
     */
    public static function getList($where,$filed,$orderFiled,$order,$page,$perPage):array
    {
       return self::where($where)
           ->orderBy($orderFiled,$order)
           ->forPage($page,$perPage)
           ->get($filed)
           ->toArray();
    }

    /**
     * @param $where
     * @return array
     */
    public static function getInfo($where,$filed):array
    {
        return self::where($where)->first($filed)->toArray();
    }

    /**
     * 创建
     * @param array $attributes
     * @return int
     */
    public static function create(array $attributes):int
    {
        return self::insert($attributes);
    }

    /**
     * 修改
     * @param array $where
     * @param array $attributes
     * @return int
     */
    public static function updated(array $where, array $attributes):int
    {
        return self::where($where)->update($attributes);
    }

}