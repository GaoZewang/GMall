<?php
/**
 * @Project   Gmall
 * @File      UserService.php
 * @Author    MrGao
 * @Date      2026/3/9 14:45
 */

namespace app\service;

use app\validate\BaseValidate;
use support\Request;
use support\Response;

class UserService
{
    /**
     * 用户列表
     * @param $params
     * @return array
     */
    public function getList($params):array
    {
        $where=[];
        if(!empty($params['username'])){
            $where[]=['username','like','%'.$params['username'].'%'];
        }
        if(!empty($params['phone'])){
            $where[]=['phone','like','%'.$params['phone'].'%'];
        }
        if(!empty($params['nickname'])){
            $where[]=['nickname','like','%'.$params['nickname'].'%'];
        }
        BaseValidate::validate($params,'list');
        $service=new BaseService('user');
        $data= $service->getListWithPage($where,['*'],'id','desc',$params['page'],$params['per_page']);
        return $data ;
    }

    /**
     * 用户详情
     * @param $params
     * @return array|object
     */
    public function getInfo($params):array| object
    {
        BaseValidate::validate($params,'info');
        $service=new BaseService('user');
        $data= $service->getInfo(['id'=>$params['id']]);
        return $data;
    }

    /**
     * 修改用户信息
     * @param $params
     * @return Response
     */
    public function editUser($params):Response
    {
        $service=new BaseService('user');
        $res= $service->edit(['id'=>$params['id']],$params);
        if($res){
            return success();
        }
        return error();

    }

    /**
     * 修改用户余额
     * @param $params
     * @return Response
     */
    public function changeBalance($params): Response
    {
        $service=new BaseService('user');
        $res= $service->edit(['id'=>$params['id']],['balance'=>$params['balance']]);
        if($res){
            return success();
        }
        return error();
    }

    /**
     * 重置用户密码
     * @param $params
     * @return Response
     */
    public function resetPassword($params): Response
    {

        $service=new BaseService('user');
        $res= $service->edit(['id'=>$params['id']],['password'=>password_hash('123456',PASSWORD_DEFAULT)]);
        if($res){
            return success();
        }
        return error();
    }
}