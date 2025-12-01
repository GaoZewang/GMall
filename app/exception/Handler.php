<?php
namespace app\exception;

use Throwable;
use Tinywan\Jwt\Exception\JwtRefreshTokenExpiredException;
use Tinywan\Jwt\Exception\JwtTokenException;
use Webman\Exception\ExceptionHandlerInterface;
use Webman\Http\Request;
use support\Response;
use support\Log;
use Respect\Validation\Exceptions\ValidationException;
use Illuminate\Database\QueryException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
class Handler implements ExceptionHandlerInterface
{
    /**
     * 记录异常日志
     */
    public function report(Throwable $exception): void
    {
        // 这里你可以按严重级别区分，这里简单写个 error
        Log::error(sprintf(
            "%s in %s:%d\nTrace: %s",
            $exception->getMessage(),
            $exception->getFile(),
            $exception->getLine(),
            $exception->getTraceAsString()
        ));
    }

    /**
     * 返回给客户端的响应
     */
    public function render(Request $request, Throwable $exception): Response
    {
        // 1）参数验证异常（workerman/validation / Respect\Validation）
        if ($exception instanceof ValidationException) {
            return json([
                'code' => 421,
                'msg'  => $exception->getMessage(),      // 简单错误
//                'errors' => $exception->getMessages(), // 如果需要每个字段的详细错误，可以打开
            ]);
        }

        // 2）业务异常示例：你自己代码里抛的 422 异常
        //    throw new \RuntimeException('xxx错误', 422);
        if ($exception instanceof \RuntimeException && $exception->getCode() === 422) {
            return json([
                'code' => 422,
                'msg'  => $exception->getMessage(),
            ]);
        }

        // 3. 资源 / 数据不存在（404）
        if ($exception instanceof ModelNotFoundException) {
            return json([
                'code' => 404,
                'msg'  => '数据不存在',
            ]);
        }

        // 4. 数据库错误（SQL 错误、连接异常等）
        if ($exception instanceof QueryException || $exception instanceof PDOException) {
            $debug = config('app.debug', false);
            return json([
                'code' => 500,
                'msg'  => $debug
                    ? $exception->getCode() . ': ' . $exception->getMessage()
                    : '数据库服务异常，请稍后再试',
            ]);
        }

        // 1. 统一处理 JWT 异常（如果没在中间件里处理）
        if ($exception instanceof JwtTokenException) {
            return json([
                'code' => $exception->getCode() ?: 401011,
                'msg'  => $exception->getMessage(),
            ]);
        }

        //刷新token时异常
        if ($exception instanceof JwtRefreshTokenExpiredException) {
            return json([
                'code' => $exception->getCode() ?: 401012,
                'msg'  => $exception->getMessage(),
            ]);
        }

        // 其他未处理的异常
        $debug = config('app.debug', false); // config/app.php 里的 debug

        if ($debug) {
            // 调试模式：把详细信息返回出来
            return json([
                'code'  => 500,
                'msg'   => $exception->getMessage(),
                'file'  => $exception->getFile(),
                'line'  => $exception->getLine(),
//                'trace' => $exception->getTrace(),
//                'trace' => $exception->getTraceAsString()

            ]);
        }

        // 生产模式：不要暴露细节
        return json([
            'code' => 500,
            'msg'  => '服务器开小差了，请稍后再试',
        ]);
    }
}
