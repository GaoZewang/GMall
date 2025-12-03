<?php
namespace app\api\controller;

use app\api\service\LoginService;
use support\Request;
use support\Response;

class LoginController
{
    /**
     * 登录
     * @param Request $request
     * @param LoginService $loginService
     * @return Response
     */
    public function login(Request $request,LoginService $loginService):Response
    {
        $username=$request->post('username');
        $password=$request->post('password');
        $token=$loginService->login($username,$password);
        return success($token);
    }

    /**
     * 注册
     * @param Request $request
     * @param LoginService $loginService
     * @return array
     */
    public function register(Request $request,LoginService $loginService):Response
    {
        $params=$request->post();
        $params['nickname']=$params['username'];
        $params['register_channel']='web';
        $params['password']=password_hash($params['password'],PASSWORD_DEFAULT);
        $token= $loginService->register($params);
        return success($token);
    }
    /**
     * 刷新token
     * @param LoginService $loginService
 * @return Response
     */
    public function refreshToken(LoginService $loginService):Response
    {
        $token=$loginService->refresh();
        return success($token);
    }

}