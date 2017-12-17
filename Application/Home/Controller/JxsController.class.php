<?php
/**
 * Created by zhang.
 * User: zhang
 * Date: 2017/10/21
 * Time: 19:25
 */

namespace Home\Controller;


class JxsController extends BaseController
{

    /**
     * 获取门票订单
     */
    public function getTickOrder()
    {
        $page = I('post.page');
        if (empty($page)) {
            $page = 0;
        } else {
            $page--;
        }
        $where['t_jsx_code'] = $this->userId;
        $orderList = M('tick_order')->where($where)->limit($page * 10, $page)->select();
        $count = M('tick_order')->where($where)->count();
        $orderList['page'] = $count;
        $this->ajaxReturn($orderList);
    }

    /**
     * 获取佣金
     */
    public function getMoney()
    {
        //获取门票佣金
        $where['t_jsx_code'] = $this->userId;
        $where['t_tick_order_type'] = 1;
        $orderList = M('tick_order')->where($where)->select();
        $tickMoney = 0;
        foreach ($orderList as $i) {
            $tickMoney = $tickMoney + ($i['t_tick_my_price'] - $i['t_tick_js_price']) * $i['t_tick_num'];
        }

        //获取景酒套餐佣金
        $swhere['o_jxs_code'] = $this->userId;
        $swhere['o_order_type'] = 1;
        $sMoney = 0;
        $sList = M('seceny_order')->where($swhere)->select();
        foreach ($sList as $s) {
            $sMoney = $sMoney + ($s['o_plane_price'] - $s['o_js_price']) * $s['o_num'];
        }

        //获取跟团游佣金
        $gwhere['g_jxs_code'] = $this->userId;
        $gwhere['g_order_type'] = 1;
        $gMoney = 0;
        $gList = M('group_order')->where($gwhere)->select();
        foreach ($gList as $g) {
            $gMoney = $gMoney + ($g['g_plane_price'] - $g['g_js_price']) * $g['g_num'];
        }
        $allMoney = $tickMoney + $sMoney + $gMoney;
        $result = M('jxs_money')->where(array('jxs_code' => $this->userId))->find();
        if (!$result) {
            $data['jxs_already_money'] = 0;
            $data['jxs_no_money'] = 0;
            $data['jxs_all_money'] = $allMoney;
            M('jxs_money')->add($data);
        } else {
            $data['jxs_no_money'] = $allMoney - $result['jxs_already_money'];
            $data['jxs_all_money'] = $allMoney;
            M('jxs_money')->where(array('jxs_code' => $this->userId))->add($data);
        }
        $lmoney = M('jxs_money')->where(array('jxs_code' => $this->userId))->find();
        $money['noMoney'] = $lmoney['jxs_no_money'];
        $money['alreadyMoney'] = $lmoney['jxs_already_money'];
        $money['allMoney'] = $lmoney['jxs_all_money'];
        $this->ajaxReturn($money);
    }


}