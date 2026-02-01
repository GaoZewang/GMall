<?php

namespace app\admin\validate;

use support\Validate;
use app\validate\BaseValidate;

class StoreValidate extends BaseValidate
{
    /**
     * 商户添加验证规则
     */
    public static function addRule(): array
    {
        return [
            'merchant_id' => Validate::intVal()->positive()->notEmpty()->setName('商户ID'),
            'admin_user_id' => Validate::intVal()->positive()->notEmpty()->setName('人员ID'),
            'name' => Validate::stringType()->lengthMax(255)->notEmpty()->setName('门店名称'),
            'address' => Validate::stringType()->lengthMax(500),
            'contact_phone' => Validate::regex('/^1[3-9]\d{9}$/')->setName('联系方式'),
            'status' => Validate::in([0, 1])->setName('状态'),
        ];
    }

    /**
     * 商户编辑验证规则
     */
    public static function editRule(): array
    {
        return [
            'id' => Validate::intVal()->positive()->notEmpty()->setName('门店ID'),
            'merchant_id' => Validate::intVal()->positive()->setName('商户ID'),
            'admin_user_id' => Validate::intVal()->positive()->setName('人员ID'),
            'name' => Validate::stringType()->lengthMax(255)->setName('门店名称'),
            'address' => Validate::stringType()->lengthMax(500),
            'contact_phone' => Validate::regex('/^1[3-9]\d{9}$/')->setName('联系方式'),
            'status' => Validate::in([0, 1])->setName('状态'),
        ];
    }

    /**
     * 详情验证规则
     */
    public static function infoRule(): array
    {
        return [
            'id' => Validate::intVal()->positive()->notEmpty()->setName('门店ID'),
        ];
    }
}