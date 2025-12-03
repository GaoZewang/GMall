<?php

namespace app\api\service;
use app\model\BaseModel;
use app\service\JwtAuthService;
use app\api\validate\UserValidate;
use support\Redis;
use Tinywan\Jwt\Exception\JwtTokenException;
use Tinywan\Jwt\JwtToken;

class LoginService
{
    /**
     * 登录
     * @param $username
     * @param $password
     * @return array|string
     */
    public function login($username, $password): array|string
    {
        if (!$username || !$password) {
            throw new \RuntimeException('用户名或密码或平台不能为空','400');
        }
        $userInfo = BaseModel::make('user')->where('username', $username)->first()->toArray();
        if (!$userInfo) {
            throw new \RuntimeException('用户不存在');
        }
        if($userInfo['status']!=1){
            throw new \RuntimeException('用户被禁用','400');
        }
        if (!password_verify($password, $userInfo['password'])) {
            throw new \RuntimeException('用户名或密码错误','400');
        }
        return JwtAuthService::createToken($userInfo['id'],'user','create');
    }

    /**
     * 注册
     * @param $data
     * @return array|string
     */
    public function register($data):array|string
    {
        UserValidate::validate($data,'add');
        $userId=BaseModel::make('user')->insertGetId($data);
        return JwtAuthService::createToken($userId,'user','create');
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
     * 登出
     * @param $request
     * @return void
     */
    public function logout($request):void
    {
        $oldToken= explode(' ',$request->header('Authorization'))[1];
        $payload = JwtAuthService::parseJwtPayload($oldToken);
        $id=$payload['extend']['id'];
        $platform = $payload['extend']['platform'];
        $key = "login:token:{$platform}:{$id}";
        Redis::del($key);
    }

}