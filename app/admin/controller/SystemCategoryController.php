<?php
/**
 * @Project   Gmall
 * @File      SystemCategoryController.php
 * @Author    MrGao
 * @Date      2025/12/4 15:03
 */

namespace app\admin\controller;

use app\validate\BaseValidate;
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
        if(!empty($data)){
            $data=buildTree($data);
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
        BaseValidate::validate($params,'info');
        $service=new BaseService('system_category');
        $data=$service->getInfo(['id'=>$params['id']]);
        if(!empty($data)){
            $data['parent_info']=[
                'id'=>0,
                'category_name'=>'顶级分类'
            ];
            if($data['parent_id']!=0){
                $data['parent_info']=$service->getInfo(['id'=>$data['parent_id']],['id','category_name']);
            }
        }
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
        SystemCategoryService::getLevelAndParentTreePath($params['parent_id'],$params);
        SystemCategoryValidate::validate($params,'add');
        $res=$service->add($params);
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
        $res=$service->edit(['id'=>$data['id']],$data);
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
        $params=$request->post();
        $service=new BaseService('system_category');
        $data=SystemCategoryService::getLevelAndParentTreePath($params['parent_id'],$params);
        SystemCategoryValidate::validate($data,'status');
        $res=$service->edit([$data['id']],['category_status'=>0]);
        if($res){
            return success();
        }
        return error();
    }
}