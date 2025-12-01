<?php
/**
 * Here is your custom functions.
 */
/**
 * 成功返回
 * @param $message
 * @return \support\Response
 */
function success($data=[],$message='ok')
{
    return json(['code' => 200, 'msg' => $message,'data'=>$data]);
}

/**
 * 错误返回
 * @param $message
 * @return \support\Response
 */
function error($message='error')
{
    return json(['code' => 1, 'msg' => $message]);
}

/**
 * 把平铺数组转成树形结构
 *
 * @param array $items
 * @return array
 */
function buildTree(array $items): array
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