<?php
/**
 * @Project   Gmall
 * @File      goodsService.php
 * @Author    MrGao
 * @Date      2025/12/7 14:55
 */

namespace app\admin\service;

use app\model\BaseModel;
use app\service\BaseService;
use support\Db;

class GoodsService
{

    /**
     * 处理json字段
     * @param array $data
     * @param string $key
     * @return string|null
     */
    private function encodeJsonField(array $data, string $key):?string
    {
        return !empty($data[$key]) ? json_encode($data[$key], JSON_UNESCAPED_UNICODE) : null;
    }

    /**
     * 获取商品信息
     * @param int $id
     * @param array $fields
     * @return array
     */
    public function getGoodsInfo(int $id,array $fields=['*']): array
    {
        $goodsModel=BaseModel::make('goods');
        $data=$goodsModel->getInfo(['id'=>$id],$fields);
        if(!empty($data)){
            $data['images']=json_decode($data['images'],true);
            $data['attrs_template']=json_decode($data['attrs_template'],true);
            $skuModel=BaseModel::make('goods_sku');
            $skuData=$skuModel->getList(['goods_id'=>$id],['*'],'id','desc');
            if(!empty($skuData)){
                foreach ($skuData as &$sku) {
                    $sku['attrs']=json_decode($sku['attrs'],true);
                }
            }else{
                $skuData=[];
            }
            $data['sku']=$skuData;
        }
        return $data;
    }

    /**
     * 创建商户商品（SPU + SKU）
     * @param int   $merchantId 商户ID
     * @param array $data       前端提交的数据（已解析为数组）
     * @return int              新建 goods_id
     */
    public  function createGoods(int $merchantId, array $data): int
    {
        // 2. 处理 JSON 字段
        $images        = $this->encodeJsonField($data, 'images');
        $attrsTemplate = $this->encodeJsonField($data, 'attrs_template');
        if (empty($data['sku_list']) || !is_array($data['sku_list'])) {
            throw new \RuntimeException('至少需要一个SKU');
        }
        Db::beginTransaction();
        try {
            $goodsModel=BaseModel::make('goods');
            $skuModel=BaseModel::make('goods_sku');
            $goodsData=[
                'merchant_id'    => $merchantId,
                'goods_name'     => $data['goods_name'],
                'subtitle'       => $data['subtitle'] ?? null,
                'category_id'    => $data['category_id'],
                'cover_image'    => $data['cover_image'] ?? null,
                'images'         => $images,
                'description'    => $data['description'] ?? null,
                'attrs_template' => $attrsTemplate,
            ];
            $goodsId=$goodsModel->insertGetId($goodsData);
            if(!$goodsId){
                throw new \RuntimeException('SPU 的 商品插入失败');
            }
            $skuRows = [];
            foreach ($data['sku_list'] as $sku) {
                if (empty($sku['attrs']) || !is_array($sku['attrs'])) {
                    throw new \RuntimeException('SKU 的 attrs 必须为数组');
                }
                $skuRows[] = [
                    'goods_id'    => $goodsId,
                    'merchant_id' => $merchantId,
                    'sku_code'    => $sku['sku_code'] ?? null,
                    'bar_code'    => $sku['bar_code'] ?? null,
                    'attrs'       => json_encode($sku['attrs'], JSON_UNESCAPED_UNICODE),
                    'cost_price'  => isset($sku['cost_price']) ? (float)$sku['cost_price'] : 0,
                    'base_price'  => isset($sku['base_price']) ? (float)$sku['base_price'] : 0,
                    'status'      => 1,
                ];
            }
            if(empty($skuRows)){
                throw new \RuntimeException('至少需要一个SKU!');
            }
            if (!$skuModel->insert($skuRows)) {
                throw new \RuntimeException('sku添加失败!');
            }
            Db::commit();
            return $goodsId;
        }catch (\Exception $e){
            Db::rollBack();
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * 修改商户商品（SPU + SKU）
     * @param int   $merchantId 商户ID
     * @param array $data       前端提交的数据（已解析为数组）
     * @return int              新建 goods_id
     */
    public  function updateGoods(int $merchantId, array $data): int
    {
        $goodsId=$data['id'];
        echo $goodsId;
        // 2. 处理 JSON 字段
        $images        = $this->encodeJsonField($data, 'images');
        $attrsTemplate = $this->encodeJsonField($data, 'attrs_template');
        if (empty($data['sku_list']) || !is_array($data['sku_list'])) {
            throw new \RuntimeException('至少需要一个SKU');
        }
        Db::beginTransaction();
        try {
            $goodsModel=BaseModel::make('goods');
            $skuModel=BaseModel::make('goods_sku');
            $goodsData=[
                'merchant_id'    => $merchantId,
                'goods_name'     => $data['goods_name'],
                'subtitle'       => $data['subtitle'] ?? null,
                'category_id'    => $data['category_id'],
                'cover_image'    => $data['cover_image'] ?? null,
                'images'         => $images,
                'description'    => $data['description'] ?? null,
                'attrs_template' => $attrsTemplate,
                'created_at' => date('Y-m-d H:i:s'),
            ];
            $res=$goodsModel->edit(['id'=>$goodsId],$goodsData);
            if(!$res){
                throw new \RuntimeException('SPU 的 商品更新失败');
            }

            //sku操作
            $oldSkuList = $skuModel->where(['goods_id'=>$goodsId,'merchant_id'=>$merchantId])->get();
            $oldSkuMap = [];
            foreach ($oldSkuList as $row) {
                $oldSkuMap[(int)$row['id']] = $row['id'];
            }
            $touchedSkuIds = []; // 这次仍然保留/更新的SKU ID

            foreach ($data['sku_list'] as $sku) {
                // 基础校验
                if (empty($sku['attrs']) || !is_array($sku['attrs'])) {
                    throw new \RuntimeException('SKU 的规格信息 attrs 必须为数组');
                }
                $skuId = isset($sku['id']) ? (int)$sku['id'] : 0;
                $rowData = [
                    'goods_id'    => $goodsId,
                    'merchant_id' => $merchantId,
                    'sku_code'    => $sku['sku_code'] ?? null,
                    'bar_code'    => $sku['bar_code'] ?? null,
                    'attrs'       => json_encode($sku['attrs'], JSON_UNESCAPED_UNICODE),
                    'cost_price'  => isset($sku['cost_price']) ? (float)$sku['cost_price'] : 0,
                    'base_price'  => isset($sku['base_price']) ? (float)$sku['base_price'] : 0,
                ];
                if ($skuId > 0 && isset($oldSkuMap[$skuId])) {
                    $rowData['updated_at']=date('Y-m-d H:i:s');
                    //  已存在的SKU，做 UPDATE
                    $skuModel->where('id', $skuId)->update($rowData);
                    $touchedSkuIds[] = $skuId;
                } else {
                    // 新增 SKU，做 INSERT
                    $newId =$skuModel->insertGetId($rowData);
                    if ($newId) {
                        $touchedSkuIds[] = $newId;
                    }
                }
            }
            //对于旧的但这次没出现的SKU → 标记为停用
            if (!empty($oldSkuMap)) {
                $oldIds = array_keys($oldSkuMap);
                $toDisableIds = array_diff($oldIds, $touchedSkuIds);
                if (!empty($toDisableIds)) {
                    $skuModel->whereIn('id', $toDisableIds)
                        ->update([
                            'status'     => 0,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                    // ⚠️ 可选：同时把门店层的 SKU 下线（如果你已经有 gm_store_goods_sku）
                    // Db::table('gm_store_goods_sku')
                    //     ->whereIn('goods_sku_id', $toDisableIds)
                    //     ->update([
                    //         'status'     => 0,
                    //         'updated_at' => $now,
                    //     ]);
                }
            }
            Db::commit();
            return $goodsId;
        }catch (\Exception $e){
            Db::rollBack();
            throw new \RuntimeException($e->getMessage());
        }
    }

    /**
     * 删除商户商品
     * @param array $where
     * @return bool
     */
    public function deleteGoods(array $where): bool
    {
        return BaseModel::make('goods')->where($where)->update(['is_deleted'=>1]);
    }
}