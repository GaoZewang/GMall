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
     * @param $username
     * @param $password
     * @param AdminUserModel $user
     * @return array
     */
    public function login($username,$password)
    {
        if (!$username || !$password) {
            throw new \RuntimeException('用户名或密码不能为空','400');
        }
        $where=['username'=>$username];
        $field=['*'];
        $userInfo=AdminUserModel::getAdminUserInfo($where,$field);
        if (!$userInfo) {
            throw new \RuntimeException('用户不存在','400');
        }
        // 这里用你自己的用户表
        if (!$userInfo || !password_verify($password, $userInfo['password'])) {
            throw new \RuntimeException('用户名或密码错误','400');
        }
        $token= JwtAuthService::createToken($userInfo['id'],'admin','create');

        return $token;  // 返回数组：access_token / refresh_token :contentReference[oaicite:1]{index=1}
    }

    public function logout($request)
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
     * @return array|string
     */
    public function refresh()
    {
        $newToken=JwtToken::refreshToken();
        $accessToken  = $newToken['access_token'];
        $payload = JwtAuthService::parseJwtPayload($accessToken);
        $uid      = $payload['extend']['id']       ?? null;
        $platform = $payload['extend']['platform'] ?? 'user';
        if (!$uid) {
            throw new JwtTokenException('token payload 缺少 id',401013);
        }
        $token= JwtAuthService::createToken($uid, $platform,'refresh',$newToken);
        return $token;
    }
}