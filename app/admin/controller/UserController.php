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
use app\service\UserService;

class UserController
{
    /**
     * 用户列表
     * @param Request $request
     * @param UserService $userService
     * @return Response
     */
    public function getList(Request $request,UserService $userService):Response
    {
        $params=$request->get();
        $data=$userService->getList($params);
        return success($data);
    }

    /**
     * 用户详情
     * @param Request $request
     * @return Response
     */
    public function getInfo(Request $request,UserService $userService):Response
    {
        $params=$request->get();
        $data=$userService->getInfo($params);
        return success($data);
    }

    /**
     * 修改用户信息
     * @param Request $request
     * @return Response
     */
    public function editUser(Request $request,UserService $userService):Response
    {
        $params=$request->post();
        unset($params['password']);
        unset($params['balance']);
        return $userService->editUser($params);
    }

    /**
     * 修改用户余额
     * @param Request $request
     * @return Response
     */
    public function changeBalance(Request $request,UserService $userService): Response
    {
        $params=$request->post();
        return $userService->changeBalance($params);
    }

    /**
     * 重置用户密码
     * @param Request $request
     * @return Response
     */
    public function resetPassword(Request $request,UserService $userService): Response
    {
        $params=$request->post();
        return $userService->resetPassword($params);
    }
}