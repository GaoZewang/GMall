<?php
/**
 * @Project   Gmall
 * @File      SystemCategoryController.php
 * @Author    MrGao
 * @Date      2025/12/4 15:03
 */

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\service\BaseService;
use app\admin\service\SystemCategoryService;
use app\admin\validate\SystemCategoryValidate;
class SystemCategoryController
{
    /**
     * 获取列表
     * @param Request $request
     * @return Response
     */
    public function getList(Request $request):Response
    {
        $where=[];
        $params=$request->get();
        if(!empty($params['category_name'])){
            $where[]=['category_name','like',"%{$params['category_name']}%"];
        }
        $service=new BaseService('system_category');
        $data=$service->getList($where);
        if(!$data){
            buildTree($data);
        }
        return success($data);
    }

    /**
     * 详情
     * @param Request $request
     * @return Response
     */
    public function getInfo(Request $request):Response
    {
        $params=$request->get();
        $service=new BaseService('system_category');
        $data=$service->getInfo(['id'=>$params['id']]);
        return success($data);
    }


    /**
     * 添加
     * @param Request $request
     * @return Response
     */
    public function createOperation(Request $request):Response
    {
        $params=$request->post();
        $service=new BaseService('system_category');
        $data=SystemCategoryService::getLevelAndParentTreePath($params['parent_id'],$params);
        SystemCategoryValidate::validate($data,'add');
        $res=$service->add($data);
        if($res){
            return success();
        }
        return error();
    }

    /**
     * 编辑
     * @param Request $request
     * @return Response
     */
    public function updateOperation(Request $request):Response
    {
        $params=$request->post();
        $service=new BaseService('system_category');
        $data=SystemCategoryService::getLevelAndParentTreePath($params['parent_id'],$params);
        SystemCategoryValidate::validate($data,'edit');
        $res=$service->edit([$data['id']],$data);
        if($res){
            return success();
        }
        return error();
    }

    /**
     * 删除
     * @param Request $request
     * @return Response
     */
    public function delOperation(Request $request):response{
        return error();
    }
}