<?php

namespace app\service\pay;

use Yansongda\Pay\Pay;

class WechatPayService
{
    public static function pay(PayContext $ctx)
    {
        return match ($ctx->scene) {

            // 微信 JSAPI（公众号/小程序）
            PayChannel::JSAPI =>
            Pay::wechat()->jsapi([
                'out_trade_no' => $ctx->orderNo,
                'description' => $ctx->title,
                'openid' => $ctx->openid,
                'amount' => [
                    'total' => intval($ctx->amount * 100),
                ],
            ]),

            // H5 支付
            PayChannel::H5 =>
            Pay::wechat()->h5([
                'out_trade_no' => $ctx->orderNo,
                'description' => $ctx->title,
                'amount' => [
                    'total' => intval($ctx->amount * 100),
                ],
            ]),

            // APP 支付
            PayChannel::APP =>
            Pay::wechat()->app([
                'out_trade_no' => $ctx->orderNo,
                'description' => $ctx->title,
                'amount' => [
                    'total' => intval($ctx->amount * 100),
                ],
            ]),

            // 扫码
            PayChannel::NATIVE =>
            Pay::wechat()->native([
                'out_trade_no' => $ctx->orderNo,
                'description' => $ctx->title,
                'amount' => [
                    'total' => intval($ctx->amount * 100),
                ],
            ]),

            default =>
            throw new \Exception('不支持的微信场景'),
        };
    }
}
