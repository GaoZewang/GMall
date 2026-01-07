<?php

namespace app\admin\controller;
use app\admin\service\AuthService;
use support\Request ;
use support\Response;

class AdminUserController
{
    /**
     * 注册
     * @param Request $request
     * @param AuthService $authService
     * @return Response
     */
    public function register(Request $request,AuthService $authService): Response
    {
        $params=$request->all();
        $res=$authService->registerAdmin($params);
        if($res) {
            return success();
        }
       return error();
    }

    /**
     * 修改密码
     * @param Request $request
     * @param AuthService $authService
     * @return Response
     */
    public function editPassword(Request $request,AuthService $authService): Response
    {
        $password=$request->post('password');
        $res=$authService->changePassword($request->uid,$password);
        if($res) {
          return success();
        }
        return error();
    }

    /**
     * 登录
     * @param Request $request
     * @param AuthService $authService
     * @return Response
     */
    public function login(Request $request,AuthService $authService):Response
    {
        $username=$request->post('username');
        $password=$request->post('password');
        $platform=$request->post('platform');
        $token=$authService->login($username,$password,$platform);
        return success($token);
    }

    /**
     * 登出
     * @param Request $request
     * @param AuthService $authService
     * @return Response
     */
    public function logout(Request $request,AuthService $authService):Response
    {
        $authService->logout($request);
        return success();
    }

    /**
     * 获取用户信息
     * @param Request $request
     * @param AuthService $authService
     * @return Response
     */
    public function getUserInfo(Request $request):Response
    {
        $userInfo=$request->user;
        $userInfo['role_name']=$request->platform;
        return success($request->user);
    }

    /**
     * 刷新token
     * @param AuthService $authService
     * @return Response
     */
    public function refreshToken(AuthService $authService):Response
    {
        $token=$authService->refresh();
        return success($token);
    }
}