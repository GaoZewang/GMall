<?php

namespace app\admin\model;

use support\Model;

class AdminUserModel extends Model
{
    /**
     * 与模型关联的表名
     *
     * @var string
     */
    protected $table = 'admin_user';

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
    public static function getAdminUserList($where,$filed,$orderFiled,$order,$page,$perPage)
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
    public static function getAdminUserInfo($where,$filed)
    {
        return self::where($where)->first($filed)->toArray();
    }

    public static function addAdminUser($data)
    {
        return self::insert($data);
    }

    public static function editAdminUser($where,$data)
    {
        return self::where($where)->update($data);
    }
}