<?php

namespace app\admin\service;


use support\Db;

class SystemPermissionService
{
    /**
     * 把平铺数组转成树形结构
     *
     * @param array $items
     * @return array
     */
    protected static function buildTree(array $items): array
    {
        // 先按 id 建一个索引
        $itemsById = [];
        foreach ($items as $item) {
            $item['children'] = [];           // 预留 children 字段
            $itemsById[$item['id']] = $item;
        }
        $tree = [];
        // 再根据 parent_id 组装
        foreach ($itemsById as $id => &$item) {
            $parentId = $item['parent_id'];

            if (is_null($parentId)) {
                // 顶级节点
                $tree[] = &$item;
            } elseif (isset($itemsById[$parentId])) {
                // 挂到父节点的 children 下
                $itemsById[$parentId]['children'][] = &$item;
            } else {
                // 找不到 parent（脏数据），可以选择丢弃或当作根节点
                $tree[] = &$item;
            }
        }
        unset($item); // 解除引用

        return $tree;
    }

    public static function del(array $ids)
    {
        $updateData=[
            'is_delete' => 1,
            'updated_at'=>date('Y-m-d H:i:s',time())
        ];
        Db::beginTransaction();
        try {
            $id=Db::table('system_permission')
                ->whereIn('id', $ids)
                ->update($updateData);
            if(!$id){
                Db::rollBack();
                throw new \Exception('删除失败');
            }
            $parentId=Db::table('system_permission')
                ->whereIn('parent_id', $ids)
                ->update($updateData);
            if(!$parentId){
                Db::rollBack();
                throw new \Exception('子级删除失败');
            }
            Db::commit();
            return true;
        }catch (\Exception $e){
            Db::rollBack();
            return $e->getMessage();
        }
    }
}