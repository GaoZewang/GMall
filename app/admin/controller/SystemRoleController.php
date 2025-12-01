<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\service\BaseService;
use app\admin\validate\SystemRoleValidate;
use app\validate\BaseValidate;
class SystemRoleController extends BaseController
{
    /**
     * 列表
     * @param Request $request
     * @return Response
     */
    public function getList(Request $request):response
    {
        $params=$request->all();
        BaseValidate::validate($params,'list');
        $filed=['id','name','description','status'];
        $service=new BaseService('system_role');
        $data=$service->getListWithPage(
            ['is_delete'=>0],$filed,'id','desc',
            $params['page'],$params['page_size']
        );
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
        $service=new BaseService('system_role');
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
        SystemRoleValidate::validate($params,'add');
        $service=new BaseService('system_role');
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
        $service=new BaseService('system_role');
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
        $params=$request->post();
        $params['updated_at']=date('Y-m-d H:i:s',time());
        BaseValidate::validate($params,'info');
        $service=new BaseService('system_role');
        if($service->edit(['id'=>$params['id']],['is_delete'=>1]))  {
            return success() ;
        }
        return error() ;
    }
}