<?php
/**
 * 数据逻辑
 */

namespace Weixin\Logic;

use Think\Model;

class OrderLogic extends Model
{
    /**
     * 微信支付完成后的跟团游订单更新
     */
    public function updateGroupOrder($orderSn)
    {
        $payStatus = M('group_order')->where(array('g_order_sn'=>$orderSn))->find();
        if (!$payStatus) {
            return array("code" =>0,"msg"=>"没有这条订单");
        }

        $all    =    $payStatus["g_child_num"] + $payStatus["g_man_num"];     //总人数
        $time   =   strtotime($payStatus["g_go_time"]);                      //出发时间
        $groupInfo = M('group_price')
            ->field('g_no_kc_num,g_need_kc_num,g_sell_num')
            ->where(array('g_user_code' => $payStatus['g_user_id'], 'g_code' => $payStatus['g_group_code'], 'unix_timestamp(g_go_time)' => $time))
            ->find();
        if (!$groupInfo) {
            return array("code"=>0,"msg"=>"没有商品价格数据");
        }
        //判断 免库存是否大于总人g_no_kc_num  大于的话 去需确认库存g_need_kc_num 如果两个都不够先扣需确认库存，订单状态变成8待确认
        $g_no_kc_num = $groupInfo["g_no_kc_num"];                       //免确认库存
        $g_need_kc_num = $groupInfo["g_need_kc_num"];                   //需确认库存
        $g_sell_num = $groupInfo["g_sell_num"];                         //价格日历销量

        if ($all < ($g_no_kc_num + $g_need_kc_num)) {
            if ($all <= $g_no_kc_num) {                                   // 总人数小于等于免确认库存
                $savewhere["g_no_kc_num"] = $g_no_kc_num - $all;         //免 = 免 - all 需不动
                $data["g_order_type"] = 2;                               // 订单状态为2未消费
            } else if ($all > $g_no_kc_num && $all < $g_need_kc_num) {    // 总人数大于免确认库存 小于需要确认库存
                $savewhere["g_need_kc_num"] = $g_need_kc_num - $all;     //需 = 需 - all 免不动
                $data["g_order_type"] = 8;                               // 订单状态为8
            } else {                                                      //总人数大于免确认库存 也大于需要确认库存
                $savewhere["g_no_kc_num"] = 0;                           //免 0
                $savewhere["g_need_kc_num"] = $g_need_kc_num - ($all - $g_no_kc_num);//需 = 免 - （ all - 免 ）
                $data["g_order_type"] = 8;                                // 订单状态为8
            }
            $AllSell = $all;                                               //销量
        } else {                                                           //总人数大于所有库存
            $savewhere["g_no_kc_num"] = 0;
            $savewhere["g_need_kc_num"] = 0;
            $data["g_order_type"] = 8;                                    // 订单状态为8
            $AllSell = $g_no_kc_num + $g_need_kc_num;
        }

        $data['g_pay_time'] = date("Y-m-d H:i:s", time());
        $Model = M();                                                           // 实例化一个空对象
//        $Model->startTrans();                                                   // 开启事务
        $om = $Model->table('lf_group_order')->where(array('g_order_sn'=>$orderSn))->data($data)->save();       //更改订单状态
//                M('group_order')->where(array('g_order_sn'=>$orderSn))->data($data)->save();
        //价格更新库存 增加价格日历销量
        $savewhere["g_sell_num"] = $all + $g_sell_num;                      //价格日历销量
        $pm = $Model->table('lf_group_price')->where(array('g_user_code' => $payStatus['g_user_id'], 'g_code' => $payStatus['g_group_code'], 'unix_timestamp(g_go_time)' => $time))->data($savewhere)->save();

//                M('group_price')->where(array('g_user_code' => $payStatus['g_user_id'], 'g_code' => $payStatus['g_group_code'], 'unix_timestamp(g_go_time)' => $time))->data($savewhere)->save();
        //增加总销量
        $gm = $Model->table("lf_group")->where(array('g_user_code' => $payStatus['g_user_id'], 'g_code' => $payStatus['g_group_code']))->setInc('g_sell', $AllSell);

//                M("group")->where(array('g_user_code' => $payStatus['g_user_id'], 'g_code' => $payStatus['g_group_code']))->setInc('g_sell', $AllSell);
        if(!$om){
            return array("code" => 0, "msg" => "订单状态保存失败" ,"num"=>$all);
        }
        if(!$gm){
            return array("code" => 2,"msg" => "总销量保存异常","num"=>$all);
        }
        if(!$pm){
            return array("code" => 2,"msg" => "价格日历数据保存异常","num"=>$all);
        }
        return array("code" => 1,"msg" => "","num"=>$all);
//        if ($om && $pm && $gm) {
////            $Model->commit();
//            return true;
//        } else {
////            $Model->rollBack();
//            return false;
//        }
    }


    /**
     * 微信支付完成后的门票订单更新
     */
    public function updateTickOrder($orderSn)
    {
        if (empty($orderSn)) {
            return array("code" =>0,"msg"=>"订单号错误");
        }
        $orderInfo = M('tick_order')->where(array('t_order_sn' => $orderSn))->find();
        if(empty($orderInfo)){
            return array("code" =>0,"msg"=>"没有这条订单");
        }
//        var_dump(M('tick_order')->_sql());
        $tickInfo = M('tick')
            ->field('t_tick_date,t_tick_sell,t_tick_kc')
            ->where(array('t_code' => $orderInfo['t_tick_code']))
            ->find();
        if (empty($tickInfo)) {
            return array("code" =>0,"msg"=>"没有商品价格数据");
        }

        $o_data['t_pay_time']           =   date("Y-m-d H:i:s", time());            //付款时间
        $o_data['t_tick_order_type']   =    2;                                      //订单状态
        $Model = M();
//        $Model->startTrans(); // 开启事务
        //todo 更新 销量 库存 改变订单状态
        $om = $Model->table('lf_tick_order')->where(array('t_order_sn' => $orderSn))->save($o_data);

        $ck_errorinfo = 1;
        if ($tickInfo['t_tick_date'] == 1 ) {       //有效期
            $ywhere['unix_timestamp(y_b_time)']     =       array('elt', strtotime($orderInfo['t_go_date']));
            $ywhere['unix_timestamp(y_e_time)']     =       array('egt', strtotime($orderInfo['t_go_date']));
            $ywhere['y_code']                         =       $orderInfo['t_tick_code'];
            $ywhere['y_user_code']                   =       $orderInfo['t_tick_id'];
            if (is_numeric($tickInfo['t_tick_kc'])) {
                if($tickInfo['t_tick_kc'] != -1){//库存
                    if(($tickInfo['t_tick_kc'] - $orderInfo['t_tick_num']) >= 0){
                        $tsdata['t_tick_kc'] = $tickInfo['t_tick_kc'] - $orderInfo['t_tick_num'];       //有效期库存
                    }else{
                        $tsdata['t_tick_kc'] = 0;           //有效期库存
                    }
                }
            }else{
                $ck_errorinfo = 0;
            }
            //有效期表跟更新销量
            $ym = $Model->table('lf_tick_y')->where(array($ywhere))->setInc('y_sell_num', $orderInfo['t_tick_num']);
        } else {
            $pwhere['p_code'] = $orderInfo['t_tick_code'];
            $pwhere['unix_timestamp(p_date)'] = array('eq', strtotime($orderInfo['t_go_date']));
            $priceInfo = M('tick_price')->where($pwhere)->find();
            if (is_numeric($priceInfo['p_ck'])) {
                if($priceInfo['p_ck'] != -1){
                    if(($priceInfo['p_ck'] - $orderInfo['t_tick_num']) >= 0){
                        $data['p_ck'] = $priceInfo['p_ck'] - $orderInfo['t_tick_num'];      //价格日历库存
                    }else{
                        $data['p_ck'] = 0;      //价格日历库存
                    }
                }
            }else{
                $ck_errorinfo = 0;
            }
            $data['p_sell_num'] = $priceInfo['p_sell_num'] + $orderInfo['t_tick_num'];       //价格日历销量
            //更新价格日历
            $ym = $Model->table('lf_tick_price')->where($pwhere)->save($data);
        }
        //主表销量
        $tsdata['t_tick_sell'] = $tickInfo['t_tick_sell'] + $orderInfo['t_tick_num'];
        $pm = $Model->table('lf_tick')->where(array('t_code' => $orderInfo['t_tick_code'], 't_user_id' => $orderInfo['t_tick_id']))->save($tsdata);

        if(!$om){
            return array("code" => 0, "msg" => "订单状态保存失败" ,"num"=>$orderInfo['t_tick_num']);
        }
        if($ck_errorinfo == 0){
            return array("code" => 2, "msg" => "价格日历或者有效期字段不是数字" ,"num"=>$orderInfo['t_tick_num']);
        }
        if(!$pm){
            return array("code" => 2,"msg" => "总销量库存保存异常","num"=>$orderInfo['t_tick_num']);
        }
        if(!$ym){
            return array("code" => 2,"msg" => "价格日历或者有效期数据保存异常","num"=>$orderInfo['t_tick_num']);
        }
        return array("code" => 1,"msg" => "","num"=>$orderInfo['t_tick_num']);
//        if ($om && $pm && $ym) {
////            $Model->commit();
//            return true;
//        } else {
////            $Model->rollBack();
//            return false;
//        }
    }

    /**
     * 微信支付完成后的酒景订单更新
     */
    public function updateSceneryOrder($orderSn)
    {
        if (empty($orderSn)) {
            return array("code" =>0,"msg"=>"订单号错误");
        }
        $orderInfo = M('seceny_order')->where(array('o_order_sn' => $orderSn))->find();
        if(!$orderInfo){
            return array("code" =>0,"msg"=>"没有这条订单");
        }
        $sinfo = M('scenery')->where(array('s_user_id' => $orderInfo['o_user_id'], 's_code' => $orderInfo['o_seceny_code']))->find();
        if (!$sinfo) {
            return array("code" =>0,"msg"=>"商品详细出错");
        }
        $Model = M();
//        $Model->startTrans(); // 开启事务

        $masterData['o_pay_time']    =   date('Y-m-d H:i:s',time());      //支付时间
        $masterData['o_order_type']  =   2;                                //订单状态
        $om = $Model->table('lf_seceny_order')->where(array('o_order_sn' => $orderSn))->save($masterData);

        $ck_errorinfo = 1;      //错误信息判断
        if ($sinfo['s_tick_date'] == 1) {
            $ywhere['y_code']                       =       $orderInfo['o_seceny_code'];
            $ywhere['y_user_id']                    =       $orderInfo['o_user_id'];
            $ywhere['unix_timestamp(y_b_time)']     =       array('eq', strtotime($orderInfo['o_date']));
            $yinfo = M('scenery_yx')->where($ywhere)->find();
            if(!$yinfo){
                $ck_errorinfo = 0;
            }else{
                if($yinfo['y_kc'] >= $orderInfo['o_num']){
                    $ydata['y_kc'] = $yinfo['y_kc'] - $orderInfo['o_num'];      //库存
                }else{
                    $ydata['y_kc'] = 0;
                }
                $ydata['y_sell_num'] = $yinfo['p_sell_num'] + $orderInfo['o_num'];  //销量
                $pm = $Model->table('lf_scenery_yx')->where($ywhere)->save($ydata);
            }
        } else {
            $pwhere['p_code']                       =           $orderInfo['o_seceny_code'];
            $pwhere['p_user_code']                  =           $orderInfo['o_user_id'];
            $pwhere['unix_timestamp(p_date)']      =            array('eq', strtotime($orderInfo['o_date']));
            $pinfo = M('seceny_price')->where($pwhere)->find();
            if(!$pinfo){
                $ck_errorinfo = 0;
            }else{
                if($pinfo['p_ck'] >= $orderInfo['o_num']){
                    $pdata['p_ck'] = $pinfo['p_ck'] - $orderInfo['o_num'];
                }else{
                    $pdata['p_ck'] = 0;
                }
                $pdata['p_sell_num'] = $pinfo['p_sell_num'] + $orderInfo['o_num'];  //销量
                $pm = $Model->table('lf_seceny_price')->where($pwhere)->save($pdata);
            }
        }
        $ym = $Model->table('lf_scenery')->where(array('s_user_id' => $orderInfo['o_user_id'], 's_code' => $orderInfo['o_seceny_code']))->setInc('s_sell', $orderInfo['o_num']);
        if(!$om){
            return array("code" => 0, "msg" => "订单状态保存失败" ,"num"=>$orderInfo['o_num']);
        }
        if($ck_errorinfo == 0){
            return array("code" => 2, "msg" => "价格日历或者有效期数字查询出错" ,"num"=>$orderInfo['o_num']);
        }
        if(!$ym){
            return array("code" => 2,"msg" => "总销量库存保存异常","num"=>$orderInfo['o_num']);
        }
        if(!$pm){
            return array("code" => 2,"msg" => "价格日历或者有效期数据保存异常","num"=>$orderInfo['o_num']);
        }
        return array("code" => 1,"msg" => "","num"=>$orderInfo['o_num']);
    }

    /**
     * 处理自费项目和过滤总价
     * zfInfo 自费项目
     * totalPrice 总价
     */
    public function addGroupValid($data)
    {
        $newdata = array();
        //totalPrice 总价
        if (empty($data["totalPrice"]) || !is_numeric($data["totalPrice"]) || $newdata['totalPrice'] < 0) {
            return array("code" => 0, "msg" => "价格必须是数字并且不小于0");
        }
        $newdata['totalPrice'] = $data["totalPrice"];

        if($data["dfcNum"]){
            if(!is_numeric($data["dfcNum"])){
                return array("code" => 0, "msg" => "单房差个数必须是数字");
            }
            $newdata['dfcNum'] = $data["dfcNum"];
        }

        if ($data["zfInfo"]) {
//            $newdata['g_zf_info'] = $data["zfInfo"];
            $newdata["zfprice"] = $this->groupZFInfo($data["zfInfo"]);//计算自费价格
//            return array("code"=>0,"msg"=>$newdata["zfprice"]);
        } else {
            $newdata["zfprice"] = 0;//计算自费价格
        }
        return array("code" => 1, "msg" => $newdata);
    }

    //跟团游自费总价格 返回自费价格
    public function groupZFInfo($str)
    {
        if (!$str) {
            return 0;
        }
        $obj = json_decode(json_encode($str),true);
        $zfprice = 0;

        foreach ($obj as $k => $v) {
            if (!(int)$v["num"]) {
                $v["num"] = 0;
            }
            $zfprice += $v["price"] * $v["num"];
        }
        return $zfprice;
    }

    public function ikbc(){

    }

}
