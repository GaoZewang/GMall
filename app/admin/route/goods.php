<?php
/**
 * @Project   Gmall
 * @File      goods.php
 * @Author    MrGao
 * @Date      2025/12/7 13:30
 */
use Webman\Route;
Route::group('',function (){
    Route::get('/list', [app\admin\controller\GoodsController::class, 'getList']);
    Route::get('/info',   [app\admin\controller\GoodsController::class, 'getInfo']);

})->middleware([
    app\middleware\JwtAuthMiddleware::class,
    app\middleware\AdminJwtMiddleware::class,
]);
