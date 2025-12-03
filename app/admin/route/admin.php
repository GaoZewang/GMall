<?php
use Webman\Route;

// 这里的实际路径是：/admin/login、/admin/refresh ...
// 因为外面 config/route.php 已经 group('/admin') 了
Route::post('/login',   [app\admin\controller\AdminUserController::class, 'login']);
Route::get('/refresh',  [app\admin\controller\AdminUserController::class, 'refreshToken']);
Route::group('', function () {
    Route::get('/logout',   [app\admin\controller\AdminUserController::class, 'logout']);
    Route::post('/register',   [app\admin\controller\AdminUserController::class, 'register']);
    Route::post('/editPassword',   [app\admin\controller\AdminUserController::class, 'editPassword']);
    Route::get('/getUserInfo',   [app\admin\controller\AdminUserController::class, 'getUserInfo']);
    Route::group('/permission', function () {
        Route::get('/list', [app\admin\controller\SystemPermissionController::class, 'getList']);
        Route::get('/info', [app\admin\controller\SystemPermissionController::class, 'getInfo']);
        Route::post('/create', [app\admin\controller\SystemPermissionController::class, 'createOperation']);
        Route::post('/update', [app\admin\controller\SystemPermissionController::class, 'updateOperation']);
        Route::post('/del', [app\admin\controller\SystemPermissionController::class, 'delOperation']);
    });
    Route::group('/role', function () {
        Route::get('/list', [app\admin\controller\SystemRoleController::class, 'getList']);
        Route::get('/info', [app\admin\controller\SystemRoleController::class, 'getInfo']);
        Route::post('/create', [app\admin\controller\SystemRoleController::class, 'createOperation']);
        Route::post('/update', [app\admin\controller\SystemRoleController::class, 'updateOperation']);
        Route::post('/del', [app\admin\controller\SystemRoleController::class, 'delOperation']);
    });

})->middleware([
    app\middleware\JwtAuthMiddleware::class,
    app\middleware\AdminJwtMiddleware::class,
]);


