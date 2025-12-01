<?php

namespace app\admin\model;

use support\Model;

class SystemPermissionModel extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'system_permission';

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
    public static function getList($where,$filed,$orderFiled,$order,$page,$perPage)
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
    public static function getInfo($where,$filed)
    {
        return self::where($where)->first($filed)->toArray();
    }

    public static function create($data)
    {
        return self::insert($data);
    }

    public static function updated($where,$data)
    {
        return self::where($where)->update($data);
    }
}