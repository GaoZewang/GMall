<?php

namespace app\service;

use app\model\Order;
use support\Db;

class PayNotifyService
{
    public static function handle($type, $orderNo, $tradeNo, $amount)
    {
        Db::transaction(function () use ($orderNo, $tradeNo, $amount) {

            $order = Order::where('order_no', $orderNo)->lockForUpdate()->first();

            if (!$order || $order->status == 1) {
                return;
            }

            // 金额校验
            if (bccomp($order->amount, $amount, 2) !== 0) {
                throw new \Exception('金额不一致');
            }

            $order->update([
                'status' => 1,
                'trade_no' => $tradeNo,
                'paid_at' => date('Y-m-d H:i:s')
            ]);

            // === 这里写你的业务 ===
            // 开通会员 / 充值点数 / 解锁功能
        });
    }
}
