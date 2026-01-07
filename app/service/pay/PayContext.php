<?php

namespace app\service\pay;

class PayContext
{
    public string $platform;   // alipay / wechat
    public string $scene;      // web / h5 / jsapi / app / native
    public string $orderNo;
    public float  $amount;
    public string $title;
    public ?string $openid = null; // JSAPI 必须
}
