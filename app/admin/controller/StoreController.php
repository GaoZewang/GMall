<?php

namespace app\admin\controller;

use app\admin\service\StoreService;
use app\admin\model\StoreModel;
use app\admin\model\AdminUserModel;
use app\admin\validate\StoreValidate;
use app\service\BaseService;
use app\validate\BaseValidate;
use support\Request;
use support\Response;

class StoreController
{
    /**
     * 店铺列表
     * @param Request $request
     * @return Response
     */
    public function getList(Request $request):response
    {
        $params=$request->all();
        BaseValidate::validate($params,'list');
        $where[]=['merchant_id','=',$params['merchant_id']];
        if(!empty($params['name'])){
            $where[]=['name','like','%'.$params['name'].'%'];
        }
        $filed=['id','merchant_id','admin_user_id','balance','revenue','name','address','contact_phone','status','created_at','updated_at'];
        $baseService = new BaseService('admin_store');
        $data=$baseService->getListWithPage($where,$filed,'id','desc',$request->page(),$request->pageSize());
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
        $storeService = new StoreService();
        $data=$storeService->getStoreDetail($params['id']);
        $data['admin_user_name']='';
        if(!empty($data)){
            $adminUserModel= new AdminUserModel();
            $adminUserInfo = $adminUserModel->getAdminUserInfo(['id'=>$data['admin_user_id']],['username']);
            $data['admin_user_name']=$adminUserInfo['username'] ?? '';
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
//        StoreValidate::validate($params,'add');
        $storeService = new StoreService();
        $result = $storeService->createStore($params);
        if($result)  {
            return success(['id' => $result]) ;
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
//        StoreValidate::validate($params,'edit');
        $storeService = new StoreService();
        $result = $storeService->updateStore($params['id'], $params);
        if($result)  {
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
        $storeService = new StoreService();
        $result = $storeService->deleteStore($params['id']);
        if($result) {
            return success() ;
        }
        return error() ;
    }
}