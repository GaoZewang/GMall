<?php
/**
 * @Project   Gmall
 * @File      PayController.php
 * @Author    MrGao
 * @Date      2026/1/7 21:23
 */

namespace app\api\controller;
use support\Request;
use app\service\pay\PayContext;
use app\service\pay\PayFactory;
use app\service\pay\PayChannel;

class PayController
{
    public function pay(Request $request)
    {
        $ctx = new PayContext();
        $ctx->platform = $request->post('platform'); // alipay / wechat
        $ctx->scene    = $request->post('scene');    // web / h5 / jsapi / app
        $ctx->orderNo  = $request->post('order_no');
        $ctx->amount   = 99.00;
        $ctx->title    = '订单支付';
        $ctx->openid   = $request->post('openid'); // JSAPI 才需要

        $result = PayFactory::pay($ctx);

        // 支付宝 web / wap
        if ($ctx->platform === PayChannel::ALIPAY) {
            return response($result->getBody()->getContents());
        }

        // 微信返回 JSON（前端拉起）
        return json($result);
    }
}