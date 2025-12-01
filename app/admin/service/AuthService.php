<?php

namespace app\admin\service;

use app\admin\model\AdminUserModel;
use app\service\JwtAuthService;
use support\Redis;
use Tinywan\Jwt\Exception\JwtTokenException;
use Tinywan\Jwt\JwtToken;

class AuthService
{

    /**
     * 登录
     * @param $username
     * @param $password
     * @param $platform
     * @return array
     */
    public function login($username,$password,$platform):array
    {
        if (!$username || !$password ||!$platform) {
            throw new \RuntimeException('用户名或密码或平台不能为空','400');
        }
        $field=['*'];
        $where=['username'=>$username];
        $model=new AdminUserModel();
        $userInfo=$model->getAdminUserInfo($where,$field);
        if (!$userInfo) {
            throw new \RuntimeException('用户不存在','400');
        }
        // 这里用你自己的用户表
        if (!password_verify($password, $userInfo['password'])) {
            throw new \RuntimeException('用户名或密码错误','400');
        }
        return JwtAuthService::createToken($userInfo['id'],$platform,'create');
    }

    public function logout($request):void
    {
        $oldToken= explode(' ',$request->header('Authorization'))[1];
        $payload = JwtAuthService::parseJwtPayload($oldToken);
        $id=$payload['extend']['id'];
        $platform = $payload['extend']['platform'];
        $key = "login:token:{$platform}:{$id}";
        Redis::del($key);
    }

    /**
     * 刷新token
     * @return array
     */
    public function refresh():array
    {
        $newToken=JwtToken::refreshToken();
        $accessToken  = $newToken['access_token'];
        $payload = JwtAuthService::parseJwtPayload($accessToken);
        $uid      = $payload['extend']['id']       ?? null;
        $platform = $payload['extend']['platform'] ?? 'user';
        if (!$uid) {
            throw new JwtTokenException('token payload 缺少 id',401013);
        }
        return JwtAuthService::createToken($uid, $platform,'refresh',$newToken);
    }

    /**
     * 修改密码
     * @param $password
     * @param $id
     * @return bool
     */
    public function changePassword($password,$id): bool
    {
        $model=new AdminUserModel();
        return $model->editAdminUser(['id'=>$id],['password'=>password_hash($password,PASSWORD_DEFAULT)]);
    }

    /**
     * 注册管理员
     * @param $params
     * @return bool
     */
    public function registerAdmin($params):bool
    {
        $model=new AdminUserModel();
        return $model->addAdminUser($params);
    }
}