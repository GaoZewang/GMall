<?php
namespace app\middleware;

use support\Redis;
use support\Request;
use Tinywan\Jwt\Exception\JwtTokenException;
use Tinywan\Jwt\JwtToken;
class JwtAuthMiddleware
{
    public function process(Request $request, callable $handler)
    {
        // 校验 token，取出 uid（如果无效/过期会抛异常）
        $uid = JwtToken::getCurrentId();
        // 再 Redis 校验：是否是当前在线 token
        $platform = JwtToken::getExtendVal('platform'); // admin / user
        $key = "login:token:{$platform}:{$uid}";
        $savedToken = Redis::get($key);
        //判断token是否刷新
        $oldToken= explode(' ',$request->header('Authorization'))[1];
        if(!$savedToken||$savedToken!=$oldToken ){
            throw new JwtTokenException('用户未登录',401013);
        }
        // 根据 user_model 配置返回 admin_user 或 user 表的数据
        $user = JwtToken::getUser();
        // 挂在 request 上，后面控制器可以直接用
        $request->uid  = $uid;
        $request->user = $user;
        return $handler($request);
    }
}
