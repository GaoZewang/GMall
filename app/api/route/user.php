<?php
use Webman\Route;

Route::group('', function () {
    Route::get('/logout',   [app\api\controller\UserController::class, 'logout']);
    Route::post('/editUser',  [app\api\controller\UserController::class, 'editUser']);
    Route::get('/userInfo',   [app\api\controller\UserController::class, 'getUserInfo']);
    Route::post('/changePassword',  [app\api\controller\UserController::class, 'changePassword']);
})->middleware([
    app\middleware\JwtAuthMiddleware::class,
]);

