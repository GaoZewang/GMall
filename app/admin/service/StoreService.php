<?php

namespace app\admin\service;

use app\admin\model\StoreModel;
use app\admin\model\MerchantModel;
use app\service\BaseService;

class StoreService
{
    /**
     * @var StoreModel
     */
    protected StoreModel $storeModel;

    public function __construct()
    {
        $this->storeModel = new StoreModel();
    }

    /**
     * 创建店铺
     * @param array $data 店铺数据
     * @return int 新增记录的ID
     */
    public function createStore(array $data): int
    {
        // 验证商户是否存在
        $merchantModel = new MerchantModel();
        $merchant = $merchantModel->getMerchantInfo(['id' => $data['merchant_id']]);
        if (empty($merchant)) {
            throw new \Exception('商户不存在');
        }

        // 设置初始值
        $data['balance'] = $data['balance'] ?? 0.00;
        $data['revenue'] = $data['revenue'] ?? 0.00;
        $data['status'] = $data['status'] ?? 1;

        return $this->storeModel->addStore($data);
    }

    /**
     * 更新店铺信息
     * @param int $storeId 店铺ID
     * @param array $data 店铺数据
     * @return int 影响的行数
     */
    public function updateStore(int $storeId, array $data): int
    {
        // 如果包含商户ID，验证其存在性
        if (isset($data['merchant_id'])) {
            $merchantModel = new MerchantModel();
            $merchant = $merchantModel->getMerchantInfo(['id' => $data['merchant_id']]);
            if (empty($merchant)) {
                throw new \Exception('商户不存在');
            }
        }

        // 移除不应该被更新的字段
        unset($data['id'], $data['balance'], $data['revenue'], $data['created_at']);

        return $this->storeModel->editStore(['id' => $storeId], $data);
    }

    /**
     * 删除店铺
     * @param int $storeId 店铺ID
     * @return int 影响的行数
     */
    public function deleteStore(int $storeId): int
    {
        return $this->storeModel->del(['id' => $storeId]);
    }

    /**
     * 获取店铺详情
     * @param int $storeId 店铺ID
     * @return array 店铺详细信息
     */
    public function getStoreDetail(int $storeId): array
    {
        $store = $this->storeModel->getStoreInfo(['id' => $storeId], ['*']);
        
        if (!empty($store)) {
            // 添加商户名称信息
            $merchantModel = new MerchantModel();
            $merchant = $merchantModel->getMerchantInfo(['id' => $store['merchant_id']], ['name']);
            $store['merchant_name'] = $merchant['name'] ?? '';
        }

        return $store;
    }

    /**
     * 根据商户ID获取店铺列表
     * @param int $merchantId 商户ID
     * @return array 店铺列表
     */
    public function getStoresByMerchant(int $merchantId): array
    {
        return $this->storeModel->getList(['merchant_id' => $merchantId], ['*'], 'id', 'desc');
    }
}