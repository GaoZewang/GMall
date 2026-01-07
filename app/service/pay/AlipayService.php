<?php

namespace app\service\pay;

use Yansongda\Pay\Pay;

class AlipayService
{
    public static function pay(PayContext $ctx)
    {
        $params = [
            'out_trade_no' => $ctx->orderNo,
            'total_amount' => $ctx->amount,
            'subject' => $ctx->title,
        ];

        return match ($ctx->scene) {
            PayChannel::WEB =>
            Pay::alipay()->web($params),

            PayChannel::H5 =>
            Pay::alipay()->wap($params),

            PayChannel::APP =>
            Pay::alipay()->app($params),

            default =>
            throw new \Exception('不支持的支付宝场景'),
        };
    }
}
