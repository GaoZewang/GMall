<?php
use Webman\Route;
Route::post('/login',   [app\api\controller\LoginController::class, 'login']);
Route::post('/register',   [app\api\controller\LoginController::class, 'register']);
Route::get('/refresh',  [app\api\controller\LoginController::class, 'refreshToken']);