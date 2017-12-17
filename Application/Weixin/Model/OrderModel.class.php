<?php

namespace Weixin\Model;

use Think\Model;

class OrderModel extends Model
{
    /**
     * 用于微信支付前的查询订单
     * @param $type 类型
     * @param $order_sn 订单号
     * @return array （code，msg）
     */
    public function getOrderInfo($type, $order_sn)
    {
        if ($type == "group") {
            return $this->getGroupOrder($order_sn);
        } else if ($type == "tick") {
            return $this->getTickOrder($order_sn);
        } else if ($type == "scenery") {
            return $this -> getSecenyOrder($order_sn);
        } else {
            return array("code" => 0, "msg" => "type参数类型不对");
        }
    }

    /**
     * @param $data 验证的数据
     * 需要参数 post
     * g_code 产品code 必须
     * menNum 成人数量 必须 数量
     * mobile 主要联系人电话 必须
     * name 主要联系人名称 必须
     * g_on_time 出发日期 必须
     * g_identification 主要联系人身份证 必须
     * g_user_id 供应商code 必须
     * childNum 儿童数量 选填
     * jxscode 经销商编码 选填
     * info 身份数组 每个人的身份证（除了主要联系人） 选填
     * remarks 备注信息 选填
     * zfInfo 自费项目数组选填  todo 目前 前端获取，后期改后端
     * todo 只是数据验证 产品code和供应商code需要判断合法性
     * g_add_order_user 是 openid
     * g_order_sn 订单号
     */
    public function addGroupValid($data)
    {
        $newdata = array();
        //产品code
        if (!$data["g_code"]) {
            return array("code" => 0, "msg" => "产品是必须的");
        }
        $newdata['g_group_code'] = $data["g_code"];

        //大人数量
        if (!$data["menNum"]) {
            return array("code" => 0, "msg" => "成人数量不能为空");
        }
        if (!is_Num($data["menNum"])) {
            return array("code" => 0, "msg" => "成人数量必须为数字");
        }
        $newdata['g_man_num'] = (int)$data["menNum"];
        if ($newdata['g_man_num'] < 1) {
            return array("code" => 0, "msg" => "成人数量不能小于1");
        }

        //手机验证
        if (!$data["mobile"]) {
            return array("code" => 0, "msg" => "手机号码必须填写");
        }
        if (!is_phone($data["mobile"])) {//正则验证手机号 common函数
            return array("code" => 0, "msg" => "手机格式错误");
        }
        $newdata['g_mobile'] = $data["mobile"];

        //主要联系人
        if (!$data["name"]) {
            return array("code" => 0, "msg" => "主要联系人是必须的");
        }
        $newdata['g_name'] = $data["name"];

        //出发时间
        if (!$data["g_on_time"]) {
            return array("code" => 0, "msg" => "出行时间必须填写");
        }
        if (!is_Date($data["g_on_time"])) {
            return array("code" => 0, "msg" => "出发日期格式不对");
        }
        $newdata['g_go_time'] = $data["g_on_time"];

        //供应商code
        if (!$data['g_user_id']) {
            return array("code" => 0, "msg" => "供应商code是必须的");
        }
        $newdata['g_user_id'] = $data['g_user_id'];

        //主要联系人身份证
        if (!$data["identification"]) {
            return array("code" => 0, "msg" => "主要联系人身份证是必须的");
        }
        if (!is_Identification_card($data["identification"])) {//正则验证身份证
            return array("code" => 0, "msg" => "主要联系人身份证号码格式错误");
        }
        $newdata["g_identification"] = $data["identification"];

        //小孩数量
        if ($data['childNum']) {
            if (!is_Num($data["childNum"])) {
                return array("code" => 0, "msg" => "儿童数量必须为数字");
            }
            $newdata['g_child_num'] = $data["childNum"];
            if ($newdata['g_child_num'] < 0) {
                return array("code" => 0, "msg" => "儿童数量格式不正确");
            }
        } else {
            $newdata['g_child_num'] = 0;
        }

        //备注信息 100
        if ($data["remarks"]) {
            $newdata["g_remark"] = $data["remarks"];//备注信息 100
        } else {
            $newdata['g_remark'] = "";
        }
        //身份数组 每个人的身份证
        if ($data["info"]) {
            $newdata["g_identity_info"] = json_encode($data["info"]);
        } else {
            $newdata['g_identity_info'] = "";
        }

        //经销商编码 todo 判断分销商有没有存在
        if ($data["jxscode"]) {
            $newdata['g_jxs_code'] = $data['jxscode'];
        } else {
            $newdata['g_jxs_code'] = "";
        }

        //自费项目
        if ($data["zfInfo"]) {
            $newdata['g_zf_info'] = json_encode($data["zfInfo"]);
        } else {
            $newdata['g_zf_info'] = "";
        }


        return array("code" => 1, "msg" => $newdata);
    }

    /**
     * 门票数据验证
     * @param $data 待验证的数据  array
     * code 产品编码 必须
     * gyscode 供应商编码 必须
     * num 数量 必须
     * name 联系人
     * date 出发日期 必须
     * mobile 手机号必须
     * jxcode 分销商code 选填
     * playerInfo 游玩信息 选填
     * totalPrice 总价 必须
     */
    public function addTickValid($data)
    {
        if (empty($data)) {
            return array("code" => 0, "msg" => "非法请求");
        }
        if (empty($data['code']) || !is_numeric($data['code'])) {
            return array("code" => 0, "msg" => "产品编码错误");
        }
        if (empty($data['gyscode']) || !is_numeric($data['gyscode'])) {
            return array("code" => 0, "msg" => "供应商编码错误");
        }
        if(empty($data['name'])){
            return array("code" => 0, "msg" => "联系人不能为空");
        }
        if (empty($data['num']) || !is_numeric($data['num']) ||  $data['num'] < 0 ) {
            return array("code" => 0, "msg" => "门票数量错误");
        }
        if(empty($data['date']) || !is_Date($data['date'])){
//            if (!is_Date($data['date'])) {
                return array("code" => 0, "msg" => "出发日期格式错误");
//            }
        }
        if (empty($data['mobile']) || !is_phone($data['mobile'])) {
            return array("code" => 0, "msg" => "手机号码错误");
        }
        if ($data['jxcode']) {
            if (!is_numeric($data['jxcode'])) {
                return array("code" => 0, "msg" => "经销商编码错误");
            }
        }
        if(empty($data['totalPrice']) || !is_numeric($data['totalPrice']) ||  $data['totalPrice'] < 0 ){
            return array("code" => 0, "msg" => "价格错误");
        }
        if ($data['identification']) {
            if (!is_Identification_card($data['identification'])) {
                return array("code" => 0, "msg" => "身份证错误");
            }
        }
        if ($data['playerInfo']) {
            $playerInfo = json_encode($data['playerInfo']);
            foreach ($playerInfo as $p) {
                if (empty($p['name'])) {
                    return array("code" => 0, "msg" => "游客联系方式名称错误");
                }
                if (empty($p['identify']) || !is_Identification_card($p['identify'])) {
                    return array("code" => 0, "msg" => "游客联系方式身份证号错误");
                }
//                if (empty($p['mobile']) || !is_phone($p['mobile'])) {
//                    return array("code" => 0, "msg" => "游客联系方式手机号码错误");
//                }
            }
        }
        return array("code" => 1, "msg" => $data);
    }

//    /**
//     * 门票检查限制数据
//     * t_tick_playerInfo.val 0 1 2 []
//     */
//    public function tickCheckLimits($code,$gysCode){
//        $res=M('tick')->field("t_tick_playerInfo")->where(array('t_user_id' => $gysCode, 't_code' => $code))->find();
//        if(!$res){
//            return array("code" => 0, "msg" => "游客联系方式手机号码错误");
//        }
//        if($res["t_tick_playerInfo"] == 0){
//
//        }else if($res["t_tick_playerInfo"] == 1){
//            I("identification");
//        }else if($res["t_tick_playerInfo"] == 2){
//            I("playerInfo");
//        }
//
//    }

    /**
     * 景酒套餐验证
     * @param $data array
     */
    public function addSecenyValid($data)
    {
        if (empty($data)) {
            return array("code" => 0, "msg" => "非法请求");
        }

        if (empty($data['code']) || !is_numeric($data['code'])) {
            return array("code" => 0, "msg" => "产品编码错误");
        }
        if (empty($data['gyscode']) || !is_numeric($data['gyscode'])) {
            return array("code" => 0, "msg" => "供应商编码错误");
        }
        if (empty($data['mobile']) || !is_phone($data['mobile'])) {
            return array("code" => 0, "msg" => "手机号码错误");
        }
        if (empty($data['name'])) {
            return array("code" => 0, "msg" => "请填写联系人信息");
        }
        if (empty($data["identification"]) || !is_Identification_card($data["identification"])) {
            return array("code" => 0, "msg" => "正确填写身份信息");
        }
        if ($data['jsxcode']) {
            if (!is_numeric($data['jsxcode'])) {
                return array("code" => 0, "msg" => "经销商编码错误");
            }
        }
        return array("code" => 1, "msg" => $data);

    }

    /**
     * @param $order_sn 跟团游订单id
     * @return array（code，msg）
     * 返回参数 商品名称name 商品总价格order_price 订单order_sn 附加attach  商品标记Goods_tag
     */
    private function getGroupOrder($order_sn)
    {
        if (!$order_sn) {
            return array("code" => 0, "msg" => "产品是必须的");
        }
        //商品名称 订单总额
        $orderInfo = M('group_order')
            ->field("g_group_name as name,g_order_price as order_price,g_order_sn as order_sn")
            ->where(array('g_order_sn=' . $order_sn))
            ->find();
        if (!$orderInfo) {
            return array("code" => 0, "msg" => "订单查询错误");
        }
        $orderInfo["attach"] = "group";
        $orderInfo["Goods_tag"] = "随意游-跟团游";

        return array("code" => 1, "msg" => $orderInfo);
    }


    /**
     * 门票下单付款
     */
    private function getTickOrder($order_sn)
    {
        if (!$order_sn) {
            return array("code" => 0, "msg" => "非法操作，请传递订单编号");
        }
        $orderInfo = M('tick_order')
            ->field('t_tick_name as name,t_tick_price as order_price,t_order_sn as order_sn')
            ->where(array('t_order_sn' => $order_sn))
            ->find();
        if (!$orderInfo) {
            return array("code" => 0, "msg" => "订单查询错误");
        }
        $orderInfo["attach"] = "tick";
        $orderInfo["Goods_tag"] = "随意游-门票";

        return array("code" => 1, "msg" => $orderInfo);
    }

    private function getSecenyOrder($order_sn){
        if (!$order_sn) {
            return array("code" => 0, "msg" => "非法操作，请传递订单编号");
        }
        $orderInfo = M('seceny_order')->field('o_name as name,o_order_price as order_price,o_order_sn as order_sn')->where(array('o_order_sn' => $order_sn))->find();
        if (!$order_sn) {
            return array("code" => 0, "msg" => "订单查询错误");
        }
        $orderInfo["attach"] = "seceny";
        $orderInfo["Goods_tag"] = "随意游-景酒套餐";

        return array("code" => 1, "msg" => $orderInfo);
    }
}