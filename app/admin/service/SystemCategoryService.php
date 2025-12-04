<?php
/**
 * @Project   Gmall
 * @File      SystemCategoryService.php
 * @Author    MrGao
 * @Date      2025/12/4 15:31
 */

namespace app\admin\service;

use app\model\BaseModel;

class SystemCategoryService
{
   public static function getLevelAndParentTreePath($parentId,&$params)
   {
       if ($parentId === 0) {
           // 顶级类目
           $level = 1;
           $parentTreePath = ''; // 顶级没有父路径
       } else {
           // 查询父类目
           $parent = BaseModel::make('system_category')
               ->where('id', $parentId)
               ->where('status', 1)       // 可选：只允许挂在启用类目下
               ->first();
           if (!$parent) {
               throw new \RuntimeException('父类目不存在或已禁用');
           }
           $level = ((int)$parent['level']) + 1;
           $parentTreePath = (string)$parent['tree_path']; // 可能为 '1/3/' 这种
       }
       $params['category_level']=$level;
       $params['tree_path']=$parentTreePath;
       return $params;

   }
}