<?php
/**
 * @Project   Gmall
 * @File      user.php
 * @Author    MrGao
 * @Date      2025/12/4 13:50
 */
use Webman\Route;
Route::group('', function () {
    Route::get('/list', [app\admin\controller\UserController::class, 'getList']);
    Route::get('/info', [app\admin\controller\UserController::class, 'getInfo']);
    Route::post('/edit', [app\admin\controller\UserController::class, 'editUser']);
    Route::post('/changeBalance', [app\admin\controller\UserController::class, 'changeBalance']);
    Route::post('/resetPassword', [app\admin\controller\UserController::class, 'resetPassword']);
})->middleware([
    app\middleware\JwtAuthMiddleware::class,
    app\middleware\AdminJwtMiddleware::class,
]);