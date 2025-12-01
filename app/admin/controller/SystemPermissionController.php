<?php

namespace app\admin\controller;

use app\admin\validate\SystemPermissionValidate;
use support\Request;
use support\Response;
use app\service\BaseService;
use app\validate\BaseValidate;
use app\admin\validate\SystemRoleValidate;
class SystemPermissionController
{
    public function getList(Request $request):response
    {
        $where=[];
        $params=$request->all();
        if($params['name']){
            $where[]=['name','like','%'.$params['name'].'%'];
        }
        if($params['url']){
            $where[]=['route_url','like','%'.$params['url'].'%'];
        }
        $filed=['id','name','code','route_url','icon','description','parent_id'];
        $service=new BaseService('system_permission');
        $data=$service->getList($where,$filed);
        //如果不为空则进行树形结构返回
        if(!empty($data)){
            $data=buildTree($data);
        }
        return success($data) ;
    }

    /**
     * 详情
     * @param Request $request
     * @return Response
     */
    public function getInfo(Request $request):response
    {
        $params=$request->all();
        BaseValidate::validate($params,'info');
        $filed=['id','name','description','status','created_at','updated_at'];
        $service=new BaseService('system_permission');
        $data=$service->getInfo(['id'=>$params['id']],$filed);
        return success($data) ;
    }

    /**
     * 添加
     * @param Request $request
     * @return Response
     */
    public function createOperation(Request $request):response
    {
        $params=$request->post();
        SystemPermissionValidate::validate($params,'add');
        $service=new BaseService('system_permission');
        if($service->add($params))  {
            return success() ;
        }
        return error() ;
    }

    /**
     * 编辑
     * @param Request $request
     * @return Response
     */
    public function updateOperation(Request $request):response
    {
        $params=$request->post();
        $params['updated_at']=date('Y-m-d H:i:s',time());
        SystemRoleValidate::validate($params,'edit');
        $service=new BaseService('system_permission');
        if($service->edit(['id'=>$params['id']],$params))  {
            return success() ;
        }
        return error() ;
    }

    /**
     * 删除
     * @param Request $request
     * @return Response
     */
    public function delOperation(Request $request):response
    {
        $params=$request->all();
        $params['updated_at']=date('Y-m-d H:i:s',time());
        SystemRoleValidate::validate($request->all(),'delete');
        $service=new BaseService('system_permission');
        if($service->edit(['id'=>$params['id']],['is_delete'=>1]))  {
            return success() ;
        }
        return error() ;
    }
}