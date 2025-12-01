<?php
namespace app\middleware;

use support\Redis;
use support\Request;
use Tinywan\Jwt\JwtToken;

class AdminJwtMiddleware
{
    public function process(Request $request, callable $handler)
    {
        // 先经过 JwtAuthMiddleware 做 token 校验（在路由上按顺序挂）
        $platform = JwtToken::getExtendVal('platform'); // admin / user
        if ($platform !== 'admin') {
            return json(['code' => 403, 'msg' => '仅后台管理员可访问']);
        }
        return $handler($request);
    }
}
