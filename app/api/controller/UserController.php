<?php
/**
 * @Project   Gmall
 * @File      UserController.php
 * @Author    MrGao
 * @Date      2025/12/3 15:45
 */

namespace app\api\controller;

use app\service\BaseService;
use support\Request;
use app\api\service\LoginService;
use support\Response;

class UserController
{
    public function getUserInfo(Request $request):Response
    {
        $userInfo=$request->user;
        return success($userInfo);
    }

    /**
     * 修改用户
     * @param Request $request
     * @return Response
     */
    public function editUser(Request $request):Response
    {
        $params=$request->post();
        $service=new BaseService('user');
        if(isset($params['password'])){
            unset($params['password']);
        }
        $res= $service->edit($request->uid,$params);
        if($res){
            return success();
        }
        return error();
    }

    /**
     * 更改密码
     * @param Request $request
     * @return Response
     */
    public function changePassword(Request $request):Response
    {
        $service=new BaseService('user');
        $password=password_hash($request->post('password'),PASSWORD_DEFAULT);
        $res= $service->edit($request->uid,['password'=>$password]);
        if($res){
            return success();
        }
        return error();
    }

    /**
     * 退出登录
     * @param Request $request
     * @param LoginService $loginService
     * @return Response
     */
    public function logout (Request $request,LoginService $loginService):Response
    {
        $loginService->logout($request);
        return success();
    }
}