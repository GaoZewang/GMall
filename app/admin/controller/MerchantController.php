<?php

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\model\BaseModel;
use app\service\BaseService;
use app\validate\BaseValidate;
use app\admin\validate\MerchantValidate;
class MerchantController
{
    /**
     * 商户列表
     * @param Request $request
     * @return Response
     */
    public function getList(Request $request):response
    {
        $params=$request->all();
        BaseValidate::validate($params,'list');
        $where[]=['is_delete','=',0];
        if($params['name']){
            $where[]=['name','like','%'.$params['name'].'%'];
        }
        $filed=['id','name','balance','revenue','logo','contact_phone','status','created_at','updated_at'];
        $service=new BaseService('admin_merchant');
        $data=$service->getListWithPage($where,$filed,'id','desc',$request->page(),$request->pageSize());
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
        $filed=['*'];
        $service=new BaseService('admin_merchant');
        $data=$service->getInfo(['id'=>$params['id']],$filed);
        $data['admin_user_name']='';
        if(!empty($data)){
            $adminUserModel= BaseModel::make('admin_user');
            $data['admin_user_name']=$adminUserModel->where(['id'=>$data['admin_user_id']])->value('username');
        }
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
        if(isset($params['balance'])){
            unset($params['balance']);
        }
        if(isset($params['revenue'])){
            unset($params['revenue']);
        }
        MerchantValidate::validate($params,'add');
        $service=new BaseService('admin_merchant');
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
        if(isset($params['balance'])){
            unset($params['balance']);
        }
        if(isset($params['revenue'])){
            unset($params['revenue']);
        }
        $params['updated_at']=date('Y-m-d H:i:s',time());
        MerchantValidate::validate($params,'edit');
        $service=new BaseService('admin_merchant');
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
        BaseValidate::validate($params,'info');
        $service=new BaseService('admin_merchant');
        $res=$service->edit(['id'=>$params['id']],['is_delete'=>1,'updated_at'=>date('Y-m-d H:i:s',time())]);
        return error($res) ;
    }
}