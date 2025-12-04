<?php
/**
 * @Project   Gmall
 * @File      UserController.php
 * @Author    MrGao
 * @Date      2025/12/4 13:30
 */

namespace app\admin\controller;

use support\Request;
use support\Response;
use app\validate\BaseValidate;
use app\service\BaseService ;

class UserController
{
    /**
     * 用户列表
     * @param Request $request
     * @return Response
     */
    public function getList(Request $request):Response
    {
        $where=[];
        $params=$request->get();
        if($params['username']){
            $where[]=['username','like',"%{$params['username']}"];
        }
        if($params['phone']){
            $where[]=['phone','like',"%{$params['nickname']}"];
        }
        if($params['nickname']){
            $where[]=['nickname','like',"%{$params['nickname']}"];
        }
        BaseValidate::validate($params,'list');
        $service=new BaseService('user');
        $data= $service->getListWithPage($where,['*'],'id','desc',$params['page'],$params['page_size']);
        return success($data);
    }

    /**
     * 用户详情
     * @param Request $request
     * @return Response
     */
    public function getInfo(Request $request):Response
    {
        $params=$request->get();
        BaseValidate::validate($params,'info');
        $service=new BaseService('user');
        $data= $service->getInfo(['id'=>$params['id']]);
        return success($data);
    }

    /**
     * 修改用户信息
     * @param Request $request
     * @return Response
     */
    public function editUser(Request $request):Response
    {
        $params=$request->post();
        unset($params['password']);
        unset($params['balance']);
        $service=new BaseService('user');
        $res= $service->edit(['id'=>$params['id']],$params);
        if($res){
            return success();
        }
        return error();

    }

    /**
     * 修改用户余额
     * @param Request $request
     * @return Response
     */
    public function changeBalance(Request $request): Response
    {
        $params=$request->post();
        $service=new BaseService('user');
        $res= $service->edit(['id'=>$params['id']],['balance'=>$params['balance']]);
        if($res){
            return success();
        }
        return error();
    }

    /**
     * 重置用户密码
     * @param Request $request
     * @return Response
     */
    public function resetPassword(Request $request): Response
    {
        $params=$request->post();
        $service=new BaseService('user');
        $res= $service->edit(['id'=>$params['id']],['password'=>password_hash('123456',PASSWORD_DEFAULT)]);
        if($res){
            return success();
        }
        return error();
    }
}