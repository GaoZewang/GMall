<?php
namespace app\middleware;

use support\Request;
use Tinywan\Jwt\JwtToken;

class UserJwtMiddleware
{
    public function process(Request $request, callable $handler)
    {
        $platform = JwtToken::getExtendVal('platform'); // admin / user

        if ($platform !== 'user') {
            return json(['code' => 403, 'msg' => '仅普通用户可访问']);
        }

        return $handler($request);
    }
}
