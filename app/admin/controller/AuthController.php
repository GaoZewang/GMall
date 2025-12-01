<?php

namespace app\admin\controller;
use app\admin\service\AuthService;
use support\Request ;

class AuthController
{

    public function register(Request $request)
    {

    }

    /**
     * 登录
     */
    public function login(Request $request,AuthService $authService)
    {
        $username=$request->post('username');
        $password=$request->post('password');
        $token=$authService->login($username,$password);
        return success($token);
    }

    /**
     * 登出
     * @param Request $request
     * @param AuthService $authService
     * @return \support\Response
     */
    public function logout(Request $request,AuthService $authService)
    {
        $authService->logout($request);
        return success();
    }

    /**
     * 刷新token
     * @param AuthService $authService
     * @return \support\Response
     */
    public function refreshToken(AuthService $authService)
    {
        $token=$authService->refresh();
        return success($token);
    }
}