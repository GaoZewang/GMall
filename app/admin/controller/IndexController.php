<?php

namespace app\admin\controller;

use support\Request;
use support\Response;

class IndexController
{
    public function index(Request $request):Response
    {
        $orderTotal = 1;
        $storeToTal=1;
        $merchantTotal=1;
        $userTotal=1;
        $data = [
            'orderTotal' => $orderTotal,
            'storeToTal' => $storeToTal,
            'merchantTotal' => $merchantTotal,
            'userTotal' => $userTotal,
        ];
        return success($data);
    }

    public function getOrderInfo()
    {
        //TODO: 获取订单信息
    }

    public function getStoreInfo()
    {
        //TODO: 获取门店信息

    }

    public function getMerchantInfo()
    {
        //TODO: 获取商户信息
    }

    public function getUserInfo()
    {
       // TODO: 获取用户信息

    }

}