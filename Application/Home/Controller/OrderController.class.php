<?php
/**
 * Created by zhang.
 * User: zhang
 * Date: 2017/10/26
 * Time: 21:44
 */

namespace Home\Controller;


class OrderController extends BaseLoginController
{



    /**
     * 门票订单下单
     */
    public function addTickOrder()
    {
        $code = I('post.code');//产品code
        $gysCode = I('post.gyscode');//供应商编码
        $jsxCode = I('post.jxcode');//经销商编码
        $num = I('post.num');//数量
        $date = I('post.date');//出行时间
        $mobile = I('post.mobile');//联系人手机号
        $name = I('post.name');
        $playInfo = json_encode(I('post.playerInfo'));//游客联系人方式 {{name：；mobile：；card：；}{name：；mobile：；card：}}
        $tickInfo = M('tick')->where(array('t_user_id' => $gysCode, 't_code' => $code))->find();
        if ($tickInfo['t_tick_date'] == 1) {
            if (empty($price)) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '非法数据'));
            }
            $markPrice = $tickInfo['t_tick_mark_price'];
            $myPrice = $tickInfo['t_tick_my_price'];
            $sePrice = $tickInfo['t_tick_settle_price'];
            $ck = $tickInfo['t_tick_kc'] - $num;
            M('tick')->where(array('t_user_id' => $gysCode, 't_code' => $code))->save(array('t_tick_kc' => $ck));
        } elseif ($tickInfo['t_tick_date'] == 2) {
            $price = M('tick_price')->where(array('p_date' => $date))->find();
            if (empty($price)) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '非法数据'));
            }
            $markPrice = $price['p_mark_price'];
            $myPrice = $price['p_my_price'];
            $sePrice = $price['p_js_price'];
            $ck = $price['p_ck'] - $num;
            M('tick_price')->where(array('p_date' => $date))->save(array('p_ck' => $ck));
        }
        $data['t_order_sn'] = $this->createOrderSn();
        $data['t_order_user_id'] = $this->userId;
        $data['t_order_user_mobile'] = $mobile;
        $data['t_order_user_name'] = $name;
        $data['t_tick_code'] = $code;
        $data['t_tick_order_type'] = 4;
        $data['t_tick_create_time'] = date("Y-m-d H:i:s", time());
        $data['t_tick_id'] = $gysCode;
        $data['t_tick_name'] = $tickInfo['t_tick_name'];
        $data['t_tick_my_price'] = $myPrice;
        $data['t_tick_num'] = $num;
        $data['t_tick_mark_price'] = $markPrice;
        $data['t_tick_js_price'] = $sePrice;
        $data['t_jsx_code'] = $jsxCode;
        $data['t_tick_order_rate'] = $tickInfo['t_tick_rate'];
        $data['t_go_date'] = $date;
        $data['t_play_info'] = $playInfo;
        $data['t_tick_price'] = $num * $myPrice;
        M('tick_order')->add($data);
        $return['orderSn'] = $data['t_order_sn'];
        $return['price'] = $data['t_tick_price'];
        $return['name'] = $tickInfo['t_tick_name'];;
        $return['num'] = $data['t_tick_num'];
        $return['date'] = $date;
        $this->ajaxReturn($return);
    }

    /**
     * 景酒套餐下单
     */
    public function addSecenyOrder()
    {
        $mobile = I('post.mobile');//联系人手机号
        $name = I('post.name');//联系人名称
        $code = I('post.gyscode');//商户编码
        $jxsCode = I('post.jxscode');//经销商编码
        $seceneyCode = I('post.code');//套餐编码
        $playInfo = json_encode(I('post.info'));//游玩人信息
        $num = I('post.num');//数量
        $date = strtotime(I('post.date'));//游玩日期
        $result = M('scenery')->where(array('s_code' => $seceneyCode, 's_user_id' => $code))->find();
        if ($result['s_tick_date'] == 1) {
            $cinfo = M('scenery_yx')->where(array('unix_timestamp(y_b_time)' => $date, 'y_code' => $seceneyCode, 'y_user_id' => $code))->find();
            if (empty($cinfo)) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '非法数据'));
            }
            $data['o_mark_price'] = $cinfo['y_mark_price'];
            $data['o_plane_price'] = $cinfo['y_my_price'];
            $data['o_js_price'] = $cinfo['y_js_price'];

        } elseif ($result['s_tick_date'] == 2) {
            $cinfo = M('seceny_price')->where(array('unix_timestamp(p_date)' => $date, 'p_code' => $seceneyCode, 'p_user_code' => $code))->find();
            if (empty($cinfo)) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '非法数据'));
            }
            $data['o_mark_price'] = $cinfo['p_mark_price'];
            $data['o_plane_price'] = $cinfo['p_my_price'];
            $data['o_js_price'] = $cinfo['p_js_price'];
        }
        $data['o_order_sn'] = $this->createOrderSn();
        $data['o_order_type'] = 4;
        $data['o_mobile'] = $mobile;
        $data['o_name'] = $name;
        $data['o_user_id'] = $code;
        $data['o_num'] = $num;
        $data['o_seceny_code'] = $seceneyCode;
        $data['o_order_price'] = $data['o_plane_price'] * $num;
        $data['o_order_time'] = date("Y-m-d H:i:s", time());
        $data['o_seceny_name'] = $result['s_name'];
        $data['o_jxs_code'] = $jxsCode;
        $data['o_order_play_info'] = $playInfo;
        $data['o_order_add_user'] = $this->userId;
        $data['o_date'] = date("Y-m-d", $date);
        $addResult = M('seceny_order')->add($data);
        $return['orderSn'] = $data['o_order_sn'];
        $return['price'] = $data['o_order_price'];
        $return['name'] = $data['o_seceny_name'];
        $return['num'] = $data['o_num'];
        $return['date'] = $date;
        if (!$addResult) {
            $this->ajaxReturn(array('code' => 0));
        } else {
            $this->ajaxReturn($return);
        }
    }

    /**
     * 跟团游下单
     */
    public function addGroupOrder()
    {
        $data['g_order_sn'] = $this->createOrderSn();
        $data['g_group_code'] = I('post.code');//产品code
        $data['g_add_order_user'] = $this->userId;
        $data['g_user_id'] = I('post.gyscode');//供应商code
        $data['g_go_time'] = I('post.goDay');//出发时
        $data['g_man_num'] = I('post.menNum');//大人数量
        $data['g_child_num'] = I('post.childNum');//儿童数量
        if (empty($data['g_child_num'])) {
            $data['g_child_num'] = 0;
        }
        $data['g_mobile'] = I('post.mobile');//联系人电话
        $data['g_name'] = I('post.name');//联系人名称
        $data['g_jxs_code'] = I('post.jxscode');//经销商编码
        $data['g_group_name'] = I('post.gname');//套餐名称
        $time = strtotime($data['g_go_time']);
        $groupInfo = M('group_price')->field('g_df_ch,g_go_time,g_man_my_price,g_man_js_price,g_child_my_price,g_child_js_price,g_df_plat,g_df_ch_close')
            ->where(array('g_user_code' => $data['g_user_id'], 'g_code' => $data['g_group_code'], 'unix_timestamp(g_go_time)' => $time))->find();
        $data['g_order_time'] = date("Y-m-d", time());//订单添加时间
        if ($groupInfo['g_df_ch'] === 'true') {
            if (I('post.dfcNum') == 0) {
                $data['g_is_dfc'] = 0;
            } else {
                $data['g_is_dfc'] = 1;
            }
            $data['g_dfc_num'] = I('post.dfcNum');
            $orderPrice = $data['g_man_num'] * $groupInfo['g_man_my_price'] + $data['g_child_num'] * $groupInfo['g_child_my_price'] + $data['g_dfc_num'] * $groupInfo['g_df_plat'];
        } else {
            $orderPrice = $data['g_man_num'] * $groupInfo['g_man_my_price'] + $data['g_child_num'] * $groupInfo['g_child_my_price'];
        }


        $data['g_order_price'] = $orderPrice;//订单总额
        $data['g_order_type'] = 4;
        $data['g_man_plane_price'] = $groupInfo['g_man_my_price'];//成人价格
        $data['g_child_plane_price'] = $groupInfo['g_child_my_price'];//儿童价格
        $data['g_child_js_price'] = $groupInfo['g_child_js_price'];//儿童结算价格
        $data['g_man_js_price'] = $groupInfo['g_man_js_price'];//大人结算价格
        $data['g_dfc_plat_price'] = $groupInfo['g_df_plat'];//大人结算价格
        $data['g_dfc_js_price'] = $groupInfo['g_df_ch_close'];//大人结算价格
        $result = M('group_order')->add($data);
        if (!$result) {
            $this->ajaxReturn(array('code' => 0));
        } else {
            $this->ajaxReturn(array('code' => 1, 'orderPrice' => $orderPrice, 'orderSn' => $data['g_order_sn'],
                'goTime' => $data['g_go_time'], 'mNum' => $data['g_man_num'], 'eNum' => $data['g_child_num'], 'gname' => $data['g_group_name'], 'dfc' => $data['g_dfc_num']));
        }
    }

    /**
     * 订单编号
     */
    public function createOrderSn()
    {
        //生成24位唯一订单号码，格式：YYYY-MMDD-HHII-SS-NNNN,NNNN-CC，其中：YYYY=年份，MM=月份，DD=日期，HH=24格式小时，II=分，SS=秒，NNNNNNNN=随机数，CC=检查码

        @date_default_timezone_set("PRC");
        //订购日期

        $order_date = date('Y-m-d');

        //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）

        $order_id_main = date('YmdHis') . rand(10000000, 99999999);

        //订单号码主体长度

        $order_id_len = strlen($order_id_main);

        $order_id_sum = 0;

        for ($i = 0; $i < $order_id_len; $i++) {

            $order_id_sum += (int)(substr($order_id_main, $i, 1));

        }

        //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）

        $order_id = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT);
        return $order_id;
    }

    /**
     * 门票支付成功通知地址
     */
    public function tickPay()
    {
        $orderSn = 'ddd';
        M('tcik_order')->where(array('t_order_sn' => $orderSn))->save(array('t_tick_order_type' => 8, 't_pay_time' => time()));
    }

    /**
     * 景酒套餐支付成功
     */
    public function secenryPay()
    {
        $orderSn = 'ddd';
        M('seceny_order')->where(array('o_order_sn' => $orderSn))->save(array('o_order_type' => 8, 'o_pay_time' => time()));
    }

    /**
     * 更团游支付成功
     */
    public function groupPay()
    {
        $orderSn = 'ddd';
        M('group_order')->where(array('g_order_sn' => $orderSn))->save(array('g_order_type' => 8, 'g_pay_time' => time()));
    }

    /**
     * 支付宝支付
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
     * 门票申请退款
     */
    public function tickRefund()
    {
        $code = I('post.orderSn');
        $id = $this->userId;
        $result = M('tick_order')->where(array('t_order_sn' => $code, 't_order_user_id' => $id))->find();
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $return = M('tick_order')->where(array('t_order_sn' => $code, 't_order_user_id' => $id))->save(array('t_tick_order_type' => 5, 't_refund_time' => time()));
        if (!$return) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '申请失败'));
        }
        $this->ajaxReturn(array('code' => '0', 'msg' => '申请成功'));
    }

    /**
     * 门票确认消费
     */
    public function tickUse()
    {
        $code = I('post.orderSn');
        if (empty($code)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $id = $this->userId;
        $result = M('tick_order')->where(array('t_order_sn' => $code, 't_order_user_id' => $id))->find();
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $return = M('tick_order')->where(array('t_order_sn' => $code, 't_order_user_id' => $id))->save(array('t_tick_order_type' => 1, 't_tick_use_time' => time()));
        if (!$return) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '消费失败'));
        }
        $this->ajaxReturn(array('code' => '0', 'msg' => '消费成功'));
    }

    /**
     * 门票取消
     */
    public function tickNo()
    {
        $code = I('post.orderSn');
        if (empty($code)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $id = $this->userId;
        $result = M('tick_order')->where(array('t_order_sn' => $code, 't_order_user_id' => $id))->find();
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $return = M('tick_order')->where(array('t_order_sn' => $code, 't_order_user_id' => $id))->save(array('t_tick_order_type' => 3));
        if (!$return) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '消费成功'));
        }
        $this->ajaxReturn(array('code' => '0', 'msg' => '申请成功'));
    }

    /**
     * 景酒申请退款
     */
    public function secenryRefund()
    {
        $code = I('post.orderSn');
        if (empty($code)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $id = $this->userId;
        $result = M('seceny_order')->where(array('o_order_sn' => $code, 'o_order_add_user' => $id))->find();
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $return = M('seceny_order')->where(array('o_order_sn' => $code, 'o_order_add_user' => $id))->save(array('o_order_type' => 5, 'o_refund_time' => time()));
        if (!$return) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '申请失败'));
        }
        $this->ajaxReturn(array('code' => '0', 'msg' => '申请成功'));
    }

    /**
     * 景酒确认消费
     */
    public function secenryUse()
    {
        $code = I('post.orderSn');
        if (empty($code)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $id = $this->userId;
        $result = M('seceny_order')->where(array('o_order_sn' => $code, 'o_order_add_user' => $id))->find();
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $return = M('seceny_order')->where(array('o_order_sn' => $code, 'o_order_add_user' => $id))->save(array('o_order_type' => 1, 'o_user_time' => time()));
        if (!$return) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '申请失败'));
        }
        $this->ajaxReturn(array('code' => '0', 'msg' => '申请成功'));
    }

    /**
     * 景酒取消
     */
    public function secenryNo()
    {
        $code = I('post.orderSn');
        if (empty($code)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $id = $this->userId;
        $result = M('seceny_order')->where(array('o_order_sn' => $code, 'o_order_add_user' => $id))->find();
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $return = M('seceny_order')->where(array('o_order_sn' => $code, 'o_order_add_user' => $id))->save(array('o_order_type' => 3));
        if (!$return) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '申请失败'));
        }
        $this->ajaxReturn(array('code' => '0', 'msg' => '申请成功'));
    }

    /**
     * 跟团游申请退款
     */
    public function groupRefund()
    {
        $code = I('post.orderSn');
        if (empty($code)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $id = $this->userId;
        $result = M('group_order')->where(array('g_order_sn' => $code, 'g_add_order_user' => $id))->find();
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $return = M('group_order')->where(array('g_order_sn' => $code, 'g_add_order_user' => $id))->save(array('g_order_type' => 5, 'g_refund_time' => time()));
        if (!$return) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '申请失败'));
        }
        $this->ajaxReturn(array('code' => '0', 'msg' => '申请成功'));
    }

    /**
     * 跟团游确认消费
     */
    public function groupUse()
    {
        $code = I('post.orderSn');
        if (empty($code)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $id = $this->userId;
        $result = M('group_order')->where(array('g_order_sn' => $code, 'g_add_order_user' => $id))->find();
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $return = M('group_order')->where(array('g_order_sn' => $code, 'g_add_order_user' => $id))->save(array('g_order_type' => 1, 'g_user_time' => time()));
        if (!$return) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '申请失败'));
        }
        $this->ajaxReturn(array('code' => '0', 'msg' => '申请成功'));
    }

    /**
     * 跟团游取消
     */
    public function groupNo()
    {
        $code = I('post.orderSn');
        if (empty($code)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $id = $this->userId;
        $result = M('group_order')->where(array('g_order_sn' => $code, 'g_add_order_user' => $id))->find();
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $return = M('group_order')->where(array('g_order_sn' => $code, 'g_add_order_user' => $id))->save(array('g_order_type' => 3));
        if (!$return) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '申请失败'));
        }
        $this->ajaxReturn(array('code' => '0', 'msg' => '申请成功'));
    }

    /**
     * 查看门票订单
     */
    public function getTickOrder()
    {
        $accountType = $this->userType;//2 经销商 1 游客
        $accountType = 1;
        $type = I('post.type');//订单情况 1 已完成 2 未消费 3 关闭取消订单 4 待付款 5退款中 6 退款成功 7 拒绝退款 8待确认订单

        if ($accountType == 1) {
            $orderList = M('tick_order')->where(array('t_order_user_id' => $this->userId, 't_tick_order_type' => $type))->select();
//            var_dump(M('tick_order') ->_sql());exit;
            $count = M('tick_order')->field('count(t_tick_order_type) as typeNum,t_tick_order_type')->where(array('t_order_user_id' => $this->userId))->group('t_tick_order_type')->select();
        } else {
            $orderList = M('tick_order')->where(array('t_jsx_code' => $this->userId, 't_tick_order_type' => $type))->select();
            $count = M('tick_order')->field('count(t_tick_order_type) as typeNum,t_tick_order_type')->where(array('t_jsx_code' => $this->userId))->group('t_tick_order_type')->select();
        }
        $tarray = ['1', '2', '3', '4', '5', '6', '7', '8'];
        $ttc = array();
        foreach ($count as $tc) {
            $ttc[] = $tc['t_tick_order_type'];
        }

        $darr = array_diff($tarray, $ttc);
        foreach ($darr as $tdraa) {
            $tmp['t_tick_order_type'] = $tdraa;
            $tmp['typeNum'] = '0';
            $count[] = $tmp;
        }
        $return['order'] = $orderList;
        $return['num'] = $count;
        $this->ajaxReturn($return);
    }

    /**
     * 查看跟团游订单
     */
    public function getGroupOrder()
    {
        $accountType = $this->userType;//2 经销商 1 游客
        $type = I('post.type');//订单情况 1 已消费 2 未消费 3 关闭取消订单 4 待付款 5退款中 6 退款成功 7 拒绝退款 8待确认订单
        if ($accountType == 1) {
            $orderList = M('group_order')->where(array('g_add_order_user' => $this->userId, 'g_order_type' => $type))->select();
            $count = M('group_order')->field('count(g_order_type) as typeNum, g_order_type')->where(array('g_add_order_user' => $this->userId))->group('g_order_type')->select();
        } else {
            $orderList = M('group_order')->where(array('g_jxs_code' => $this->userId, 'g_order_type' => $type))->select();
            $count = M('group_order')->field('count(g_order_type) as typeNum, g_order_type')->where(array('g_jxs_code' => $this->userId))->group('g_order_type')->select();
        }
        $tarray = ['1', '2', '3', '4', '5', '6', '7', '8'];
        $ttc = array();
        foreach ($count as $tc) {
            $ttc[] = $tc['t_tick_order_type'];
        }

        $darr = array_diff($tarray, $ttc);
        foreach ($darr as $tdraa) {
            $tmp['t_tick_order_type'] = $tdraa;
            $tmp['typeNum'] = '0';
            array_merge($count, $tmp);
        }
        $return['order'] = $orderList;
        $return['num'] = $count;
        $this->ajaxReturn($return);
    }

    /**
     * 查看景就套餐订单
     */
    public function getSeceneyOrder()
    {
        $accountType = $this->userType;//2 经销商 1 游客
        $type = I('post.type');
        if ($accountType == 1) {
            $orderList = M('seceny_order')->where(array('o_order_add_user' => $this->userId, 'o_order_type' => $type))->select();
            $count = M('seceny_order')->field('count(o_order_type) as typeNum,o_order_type')->where(array('o_order_add_user' => $this->userId))->group('o_order_type')->select();
        } else {
            $orderList = M('seceny_order')->where(array('o_jxs_code' => $this->userId, 'o_order_type' => 1, 'o_order_type' => $type))->select();
            $count = M('seceny_order')->field('count(o_order_type) as typeNum,o_order_type')->where(array('o_jxs_code' => $this->userId))->group('o_order_type')->select();
        }
        $tarray = ['1', '2', '3', '4', '5', '6', '7', '8'];
        $ttc = array();
        foreach ($count as $tc) {
            $ttc[] = $tc['t_tick_order_type'];
        }
        $darr = array_diff($tarray, $ttc);
        if (!empty($darr)) {
            foreach ($darr as $tdraa) {
                $tmp['t_tick_order_type'] = $tdraa;
                $tmp['typeNum'] = '0';
                array_merge($count, $tmp);
            }
        }

        $return['order'] = $orderList;
        $return['num'] = $count;
        $this->ajaxReturn($return);
    }
}