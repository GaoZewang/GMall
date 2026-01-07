<?php

namespace app\service\pay;

use Yansongda\Pay\Pay;

class PayFactory
{
    public static function pay(PayContext $ctx)
    {
        Pay::config(config('payment'));

        return match ($ctx->platform) {
            PayChannel::ALIPAY => AlipayService::pay($ctx),
            PayChannel::WECHAT => WechatPayService::pay($ctx),
            default => throw new \Exception('不支持的支付平台'),
        };
    }
}
