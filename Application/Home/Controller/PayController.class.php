<?php
/**
 * Created by zhang.
 * User: zhang
 * Date: 2017/11/6
 * Time: 22:03
 */

namespace Home\Controller;


class PayController extends BaseLoginController
{

    /**
     * 订单支付宝支付
     */
    public function alipayOrder()
    {
        Vendor('AlipaySdk.AopSdk');
        $aop = new \AopClient ();
        $aop->gatewayUrl = 'https://openapi.alipaydev.com/gateway.do';
        $aop->appId = '3333333333';
        $aop->rsaPrivateKey = '见密钥生成工具';
        $aop->apiVersion = '1.0';
        $aop->signType = 'RSA2';
        $aop->postCharset = 'utf-8';
        $aop->format = 'json';
        $request = new \AlipayTradePagePayRequest ();
        $request->setReturnUrl('http://v1.xxxx.com/Home/Test/order');
        $request->setNotifyUrl('http://v1.xxxxx.com');
        $request->setBizContent(
            '{"product_code":"FAST_INSTANT_TRADE_PAY",
        "out_trade_no":"20170320010101002",
        "subject":"Iphone7 16G",
        "total_amount":"18.88",
        "body":"Iphone6 16G"}');
        $result = $aop->execute($request);
    }

    /**
     * 微信支付
     */

    /**
     * 门票支付成功通知地址
     */
    public function tickPay()
    {
        $orderSn = 'ddd';
        $orderInfo = M('tick_order')->field('t_tick_code,t_tick_num,t_go_date')->where(array('t_order_sn' => $orderSn))->find();
        $tickInfo = M('tick')->field('t_tick_date')->where(array('t_code' => $orderInfo['t_tick_code']))->find();
        if ($tickInfo['t_tick_date'] == 1) {
            M('tick_y')->where(array('y_code' => $orderInfo['t_tick_code']))->setInc('y_sell_num', $orderInfo['t_tick_num']);
            M('tick')->where(array('t_code' => $orderInfo['t_tick_code']))->setDec(array('t_tick_kc', $orderInfo['t_tick_num']));
        } else {
            M('tick_price')->where(array('p_code' => $orderInfo['t_tick_code'], 'unix_timestamp(p_date)' => strtotime($orderInfo['t_go_date'])))->setInc('p_sell_num', $orderInfo['t_tick_num']);
            M('tick_price')->where(array('p_code' => $orderInfo['t_tick_code'], 'unix_timestamp(p_date)' => strtotime($orderInfo['t_go_date'])))->setDec('p_k', $orderInfo['t_tick_num']);
        }
        M('tcik_order')->where(array('t_order_sn' => $orderSn))->save(array('t_tick_order_type' => 8, 't_pay_time' => time()));
    }

    /**
     * 景酒套餐支付成功
     */
    public function secenryPay()
    {
        $orderSn = 'ddd';
        $orderInfo = M('seceny_order')->field('o_seceny_code,o_num,o_date')->where(array('o_order_sn' => $orderSn))->find();
        $secenyInfo = M('scenery')->field('s_tick_date')->where(array('s_code' => $orderInfo['o_seceny_code']))->find();
        if ($secenyInfo['s_tick_date'] == 1) {
            M('scenery_yx')->where(array('y_code' => $orderInfo['o_seceny_code'], 'unix_timestamp(y_b_time)' => $orderInfo['o_date']))->setInc('y_sell_num', $orderInfo['o_num']);
            M('scenery_yx')->where(array('y_code' => $orderInfo['o_seceny_code'], 'unix_timestamp(y_b_time)' => $orderInfo['o_date']))->setDec('y_kc', $orderInfo['o_num']);
        } else {
            M('seceny_price')->where(array('p_code' => $orderInfo['o_seceny_code'], 'unix_timestamp(p_date)' => $orderInfo['o_date']))->setInc(array('p_sell_num' => $orderInfo['o_num']));
            M('seceny_price')->where(array('p_code' => $orderInfo['o_seceny_code'], 'unix_timestamp(p_date)' => $orderInfo['o_date']))->setDec(array('p_ck' => $orderInfo['o_num']));
        }
        M('seceny_order')->where(array('o_order_sn' => $orderSn))->save(array('o_order_type' => 8, 'o_pay_time' => time()));
    }

    /**
     * 更团游支付成功
     */
    public function groupPay()
    {
        $orderSn = 'ddd';
        $orderInfo = M('group_order')->field('g_group_code,g_go_time,g_man_num')->where(array('g_order_sn' => $orderSn))->find();
        M('group_price')->where(array('g_code' => $orderInfo['g_group_code'], 'unix_timestamp(g_go_time)' => strtotime($orderInfo['g_go_time'])))->setInc('g_sell_num', $orderInfo['g_man_num']);
        M('group_price')->where(array('g_code' => $orderInfo['g_group_code'], 'unix_timestamp(g_go_time)' => strtotime($orderInfo['g_go_time'])))->setDec('g_need_kc_num', $orderInfo['g_man_num']);
        M('group_order')->where(array('g_order_sn' => $orderSn))->save(array('g_order_type' => 8, 'g_pay_time' => time()));
    }

}