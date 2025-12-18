<?php
/**
 * @Project   Gmall
 * @File      BaseMiddleware.php
 * @Author    MrGao
 * @Date      2025/12/18 13:11
 */

namespace app\middleware;

use support\Request;


class BaseMiddleware
{
    public function process(Request $request, callable $handler)
    {
        return $handler($request);
    }
}