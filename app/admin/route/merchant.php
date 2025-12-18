<?php
/**
 * @Project   Gmall
 * @File      merchant.php
 * @Author    MrGao
 * @Date      2025/12/18 13:06
 */
use Webman\Route;
Route::group('',function (){
    Route::get('/list',    [app\admin\controller\AdminMerchantController::class, 'getList']);
    Route::get('/info',    [app\admin\controller\AdminMerchantController::class, 'getInfo']);
//    Route::get('/delete',  [app\admin\controller\AdminMerchantController::class, 'deleteOption']);
    Route::post('/create', [app\admin\controller\AdminMerchantController::class, 'createOperation']);
//    Route::post('/update', [app\admin\controller\AdminMerchantController::class, 'updateOperation']);
//    Route::post('/status', [app\admin\controller\AdminMerchantController::class, 'updateGoodsStatus']);

})->middleware([
    app\middleware\BaseMiddleware::class,
    app\middleware\JwtAuthMiddleware::class,
    app\middleware\AdminJwtMiddleware::class,
]);