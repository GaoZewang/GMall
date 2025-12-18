<?php
/**
 * @Project   Gmall
 * @File      merchant.php
 * @Author    MrGao
 * @Date      2025/12/18 13:06
 */
use Webman\Route;
Route::group('',function (){
    //商户管理
    Route::get('/list',    [app\admin\controller\MerchantController::class, 'getList']);
    Route::get('/info',    [app\admin\controller\MerchantController::class, 'getInfo']);
    Route::get('/delete',  [app\admin\controller\MerchantController::class, 'delOperation']);
    Route::post('/create', [app\admin\controller\MerchantController::class, 'createOperation']);
    Route::post('/update', [app\admin\controller\MerchantController::class, 'updateOperation']);
    //店铺管理
    Route::get('/storeList',    [app\admin\controller\StoreController::class, 'getList']);
    Route::get('/storeInfo',    [app\admin\controller\StoreController::class, 'getInfo']);
    Route::get('/storeDelete',  [app\admin\controller\StoreController::class, 'delOperation']);
    Route::post('/storeCreate', [app\admin\controller\StoreController::class, 'createOperation']);
    Route::post('/storeUpdate', [app\admin\controller\StoreController::class, 'updateOperation']);
})->middleware([
    app\middleware\BaseMiddleware::class,
    app\middleware\JwtAuthMiddleware::class,
    app\middleware\AdminJwtMiddleware::class,
]);