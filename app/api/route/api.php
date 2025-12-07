<?php
use Webman\Route;
Route::post('/login',   [app\api\controller\LoginController::class, 'login']);
Route::post('/register',   [app\api\controller\LoginController::class, 'register']);
Route::get('/refresh',  [app\api\controller\LoginController::class, 'refreshToken']);
//后台文件上传
Route::group('/upload', function () {
    Route::post('/single',   [app\BaseController::class, 'single']);
    Route::post('/multi',   [app\BaseController::class, 'single']);
})->middleware([
    app\middleware\JwtAuthMiddleware::class,
]);