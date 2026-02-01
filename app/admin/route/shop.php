<?php
/**
 * @Project   Gmall
 * @File      shop.php
 * @Author    MrGao
 * @Date      2025/12/18 13:06
 */
use Webman\Route;
Route::group('',function (){
    //店铺管理
    Route::get('/list',    [app\admin\controller\StoreController::class, 'getList']);
    Route::get('/info',    [app\admin\controller\StoreController::class, 'getInfo']);
    Route::get('/delete',  [app\admin\controller\StoreController::class, 'delOperation']);
    Route::post('/create', [app\admin\controller\StoreController::class, 'createOperation']);
    Route::post('/update', [app\admin\controller\StoreController::class, 'updateOperation']);
})->middleware([
    app\middleware\BaseMiddleware::class,
    app\middleware\JwtAuthMiddleware::class,
    app\middleware\AdminJwtMiddleware::class,
]);