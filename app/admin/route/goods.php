<?php
/**
 * @Project   Gmall
 * @File      goods.php
 * @Author    MrGao
 * @Date      2025/12/7 13:30
 */
use Webman\Route;
Route::group('',function (){
    Route::get('/list',    [app\admin\controller\GoodsController::class, 'getList']);
    Route::get('/info',    [app\admin\controller\GoodsController::class, 'getInfo']);
    Route::get('/delete',  [app\admin\controller\GoodsController::class, 'deleteOption']);
    Route::post('/create', [app\admin\controller\GoodsController::class, 'createOption']);
    Route::post('/update', [app\admin\controller\GoodsController::class, 'updateOption']);
    Route::post('/status', [app\admin\controller\GoodsController::class, 'updateGoodsStatus']);

})->middleware([
    app\middleware\BaseMiddleware::class,
    app\middleware\JwtAuthMiddleware::class,
    app\middleware\AdminJwtMiddleware::class,
]);
