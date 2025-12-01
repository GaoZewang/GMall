<?php

namespace app\service;

use support\Redis;
use Tinywan\Jwt\Exception\JwtTokenException;
use Tinywan\Jwt\JwtToken;

class JwtAuthService
{

    public static function createToken($id,$platform,$option,$token=''): array
    {
        $ttl=7200;//有效期
        $key = "login:token:{$platform}:{$id}";//缓存key
        //如果是创建
        if($option=='create'){
            // JWT 负载：id 必须是全局唯一 id
            $payload = [
                'id'   => $id,
                // 自定义扩展字段，比如角色、多端区分等
                'client' => JwtToken::TOKEN_CLIENT_WEB, // 或 TOKEN_CLIENT_MOBILE 等
                'platform' => $platform,
            ];
            $token = JwtToken::generateToken($payload);//生成token
            Redis::setex($key, $ttl, $token['access_token']);//缓存token
        }elseif ($option=='refresh'){//刷新
            Redis::setex($key, $ttl, $token['access_token']);//缓存token
        }else{
            throw new JwtTokenException('参数错误',401014);
        }
        return $token;  // 返回数组：access_token / refresh_token :contentReference[oaicite:1]{index=1}
    }

    public static function parseJwtPayload(string $jwt): array
    {
        $parts = explode('.', $jwt);
        if (count($parts) < 2) {
            return [];
        }
        $payload = $parts[1];

        // base64url => base64
        $payload = strtr($payload, '-_', '+/');
        $payload .= str_repeat('=', 3 - (strlen($payload) + 3) % 4);

        $json = base64_decode($payload);
        return json_decode($json, true) ?: [];
    }
}