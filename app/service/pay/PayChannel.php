<?php

namespace app\service\pay;

class PayChannel
{
    // 支付平台
    const ALIPAY = 'alipay';
    const WECHAT = 'wechat';

    // 终端类型
    const WEB    = 'web';     // PC
    const H5     = 'h5';      // 手机浏览器
    const JSAPI  = 'jsapi';   // 微信公众号/小程序
    const APP    = 'app';     // 原生 APP
    const NATIVE = 'native';  // 扫码
}
