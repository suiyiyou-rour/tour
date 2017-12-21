<?php
/**
 * 订单 以及详情
 */

namespace Weixin\Controller;
class OrderController extends BaseController
{
    public $user_account;

    public function __construct()
    {
        parent::__construct();
        header("Content-Type:text/html;charset=utf-8");
        #todo 判断登陆状态
         $this->user_account = $_SESSION["online_use_info"]["user_account"];
//         $this->user_account="18060481803";        //测试
         if (!$this->user_account) {
             $this->ajaxReturn(array('code' => 403, "msg" => "没有登陆或者登陆超时"));
         }
    }

    public function index(){

    }

    /**
     *  订单跳转数据请求接口
     */
    public function getOrderDetails()
    {
        $mark = I('post.shopType');
        $orderSn = I('post.orderSn');
        if (empty($mark) || empty($orderSn)) {
            $this->ajaxReturn(array('code' => 304, "msg" => "缺少参数"));
        }
        if ($mark == "group") {
            $this->getOrderGroupDetails($orderSn);
        } else if ($mark == "tick") {
            $this->getTickOrderDetail($orderSn);
        } else if ($mark == "scenery") {
            $this->getSecenyOrderDetail($orderSn);
        } else {
            $this->ajaxReturn(array('code' => 304, "msg" => "参数类型错误"));
        }
    }

    /**
     * 跟团游下单
     */
    public function addGroupOrder()
    {
        #todo 判断必须参数
//        $data['g_code'] = "123123123";//产品code  -- 测试数据
//        $data['menNum'] = "1";//大人数量
//        $data['childNum'] = "1";//儿童数量
//        $data['mobile'] = "18060481803";//主要联系人电话
//        $data['name'] = "lizu";//主要联系人名称
//        $data['g_on_time'] = "2017-11-22";//出发日期
//        $data['jxscode'] = "12312312312321312";//经销商编码 暂时没有
//        $data['g_user_id'] = "334451544";//供应商code
//        $data["info"]="";//身份数组 每个人的身份证
//        $data["remarks"]="";//备注信息 100
//        $data["identification"]="352201199303141637";//主要联系人身份证

        $postdata = I("post.");
        //验证数据库写入数据
        $results = D("Order")->addGroupValid($postdata);
        if ($results["code"] == 0) {
            $this->ajaxReturn(array('code' => 403, "msg" => $results["msg"]));
        }
        $data = $results["msg"];
        //处理自费价格对比 数据
        $res = D("Order", "Logic")->addGroupValid($postdata);
        if ($res["code"] == 0) {
            $this->ajaxReturn(array('code' => 403, "msg" => $results["msg"]));
        }
        $compareata = $res["msg"];

        #todo 查询订单
        //供应商code 和 套餐名称  $data['g_user_id'] = $group["g_user_code"];//供应商code
        $group = M('group')->field("g_name")->where(array('g_code' => I("g_code")))->find();
        if (!$group) {
            $this->ajaxReturn(array('code' => 304, "msg" => "产品信息错误！"));
        }

        $time = strtotime($data['g_go_time']);//出发时间
        $groupInfo = M('group_price')
            ->field('g_no_kc_num,g_need_kc_num,g_max_buy_num,g_min_buy_num,g_df_ch,g_go_time,g_man_my_price,g_man_js_price,g_child_my_price,g_child_js_price,g_df_plat,g_df_ch_close,g_is_open')
            ->where(array('g_user_code' => $data['g_user_id'], 'g_code' => $data['g_group_code'], 'unix_timestamp(g_go_time)' => $time))
            ->find();
        if (!$groupInfo) {
            $this->ajaxReturn(array('code' => 304, "msg" => "产品信息详情错误！"));
        }
        #todo 库存检验
        if($groupInfo["g_is_open"] != 1){
            $this->ajaxReturn(array('code' => 304, "msg" => "库存已经被关闭！"));
        }
        $totalNumber = $data['g_man_num'] + $data['g_child_num'];
        if ($groupInfo["g_max_buy_num"]) {
            if ($totalNumber > $groupInfo["g_max_buy_num"]) {
                $this->ajaxReturn(array('code' => 304, "msg" => "每单最多购买人数,不能超过" . $groupInfo["g_max_buy_num"] . "人", "data" => $totalNumber));
            }
        }
        if ($groupInfo["g_min_buy_num"]) {
            if ($totalNumber < $groupInfo["g_min_buy_num"]) {
                $this->ajaxReturn(array('code' => 304, "msg" => "每单最少购买人数,不能小于" . $groupInfo["g_max_buy_num"] . "人"));
            }
        }

        if ($totalNumber > ($groupInfo["g_no_kc_num"] + $groupInfo["g_need_kc_num"])) {
            $this->ajaxReturn(array('code' => 304, "msg" => "库存已经不够，目前还剩" . ($groupInfo["g_no_kc_num"] + $groupInfo["g_need_kc_num"]) . "人"));
        }

        $data['g_order_time'] = date("Y-m-d", time());//订单添加时间
        if ($groupInfo['g_df_ch'] === 'true') {//判断是否开启单房差
            if ($compareata["dfcNum"] == 0) {
                $data['g_is_dfc'] = 0;
            } else {
                $data['g_is_dfc'] = 1;
            }
            $data['g_dfc_num'] = $compareata["dfcNum"];
//            //成人数量+儿童数量 % 2
//            if(($data['g_man_num'] + $data['g_child_num']) % 2){
//                $data['g_dfc_num']=1;
//            }

            //成人数量 * 成人价格 +  儿童数量 * 儿童价格 + 单房差个数 * 单房差市场价 + 自费价格
            $orderPrice = $data['g_man_num'] * $groupInfo['g_man_my_price'] + $data['g_child_num'] * $groupInfo['g_child_my_price'] + $data['g_dfc_num'] * $groupInfo['g_df_plat'] + $compareata["zfprice"];
        } else {
            //成人数量 * 成人价格 +  儿童数量 * 儿童价格 + 自费价格
            $orderPrice = $data['g_man_num'] * $groupInfo['g_man_my_price'] + $data['g_child_num'] * $groupInfo['g_child_my_price'] + $compareata["zfprice"];
        }

        //判断前端传过来的价格和计算的对比
        if ((int)($orderPrice * 100) !== (int)($compareata['totalPrice'] * 100)) {
            $this->ajaxReturn(array('code' => 304, "msg" => "金额计算异常"));
        }

        $data['g_jxs_code'] = cookie('pid');                                 //经销商编码
        $data['g_group_name']         =     $group["g_name"];                //供应商产品名称
        $data['g_add_order_user']     =     $this->user_account;            //下单用户
        $data['g_order_sn']           =     $this->createOrderSn();           //订单号
        $data['g_order_price']  = $orderPrice;                                //订单总额
        $data['g_order_type'] = 4;                                            //订单状态
        $data['g_man_plane_price'] = $groupInfo['g_man_my_price'];          //成人价格
        $data['g_child_plane_price'] = $groupInfo['g_child_my_price'];      //儿童价格
        $data['g_child_js_price'] = $groupInfo['g_child_js_price'];         //儿童结算价格
        $data['g_man_js_price'] = $groupInfo['g_man_js_price'];             //大人结算价格
        $data['g_dfc_plat_price'] = $groupInfo['g_df_plat'];                //大人结算价格
        $data['g_dfc_js_price'] = $groupInfo['g_df_ch_close'];              //大人结算价格




        $result = M('group_order')->add($data);
//        var_dump(M('group_order')->_sql());
        if (!$result) {
            $this->ajaxReturn(array('code' => 304, "msg" => "订单生成出错"));
        } else {
            $this->ajaxReturn(array('code' => 200, 'data' => array('orderPrice' => $orderPrice,
                                                    'orderSn' => $data['g_order_sn'],
                                                    'goTime' => $data['g_go_time'],
                                                    'mNum' => $data['g_man_num'],
                                                    'eNum' => $data['g_child_num'],
                                                    'gname' => $data['g_group_name'],
                                                    'dfc' => $data['g_dfc_num'])));
        }
    }

    /**
     * 门票订单下单
     */
    public function addTickOrder()
    {
        $postData = I("post.");
        //验证数据库写入数据
        $results = D("Order")->addTickValid($postData);
        if ($results["code"] == 0) {
            $this->ajaxReturn(array('code' => 403, "msg" => $results["msg"]));
        }
        $postData = $results['msg'];
        $code         =     $postData['code'];              //产品code
        $gysCode      =     $postData['gyscode'];           //供应商编码
        $jsxCode      =     cookie('pid');                  //经销商编码
        $num          =     (int)$postData['num'];          //数量
        $date         =     $postData['date'];              //出行时间 //todo 有效期可以没有
        $mobile       =     $postData['mobile'];            //联系人手机号
        $name         =     $postData['name'];              //联系人名字
        $IdCard       =     $postData['identification'];   //身份证
        $playerInfo   =     $postData['playerInfo'];        //游玩人信息

//        $playInfo = json_encode($postData['playerInfo']);//游客联系人方式 {{name：；mobile：；card：；}{name：；mobile：；card：}}
        $tickInfo = M('tick')->where(array('t_user_id' => $gysCode, 't_code' => $code))->find();
        if (empty($tickInfo)) {
            $this->ajaxReturn(array('code' => 403, 'msg' => '非法数据'));
        }

        # TODO 判断是否需要身份证
        if($tickInfo["t_tick_playerInfo"] == 1){
            if(empty($IdCard)){
                $this->ajaxReturn(array('code' => 403, "msg" => "身份证是必须的"));
            }
            $data['identification'] = $IdCard;
        }else if( $tickInfo["t_tick_playerInfo"] == 2 ){
            if(empty($IdCard) || empty($playerInfo)){
                $this->ajaxReturn(array('code' => 403, "msg" => "游玩人信息是必须的"));
            }
            $data['t_identification'] = $IdCard;
            $data['t_play_info'] = json_encode($playerInfo);
        }

        # TODO 判断价格模式
        if ($tickInfo['t_tick_date'] == 1) {
            //判断库存

            if($tickInfo['t_tick_kc'] != -1){
                if ($tickInfo['t_tick_kc'] < $num) {
                    $this->ajaxReturn(array('code' => 304, "msg" => "库存已经不够，目前还剩" . ($tickInfo['t_tick_kc']) . "人"));
                }
            }
            $markPrice  =  $tickInfo['t_tick_mark_price'];      //市场价
            $myPrice    =  $tickInfo['t_tick_my_price'];        //平台价格
            $sePrice    =  $tickInfo['t_tick_settle_price'];    //结算价格
//            $ck = $tickInfo['t_tick_kc'] - $num;
//            M('tick')->where(array('t_user_id' => $gysCode, 't_code' => $code))->save(array('t_tick_kc' => $ck));
            $data['t_go_date'] = $date;//todo 有效期出发日期为空
        } elseif ($tickInfo['t_tick_date'] == 2) {
            $priceInfo = M('tick_price')->where(array('p_date' => $date,'p_code' => $code))->find();
            if (empty($priceInfo)) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '非法数据'));
            }
            //判断库存
            if($priceInfo["p_is_open"] != 1){
                $this->ajaxReturn(array('code' => '0', 'msg' => '库存已经被关闭！'));
            }
            if($priceInfo['p_ck'] && $priceInfo['p_ck'] != -1){    //空也是无限库存
                if ( $priceInfo['p_ck'] < $num ) {
                    $this->ajaxReturn(array('code' => 304, "msg" => "库存已经不够，目前还剩" . ($priceInfo['p_ck']) . "人"));
                }
            }


            $markPrice  =  $priceInfo['p_mark_price'];      //市场价
            $myPrice    =  $priceInfo['p_my_price'];        //平台价格
            $sePrice    =  $priceInfo['p_js_price'];        //结算价格
//            $ck = $priceInfo['p_ck'] - $num;
//            M('tick_price')->where(array('p_date' => $date))->save(array('p_ck' => $ck));
            $data['t_go_date'] = $date;     //出行日期
        }

        $data['t_order_sn']             =       $this->createOrderSn();             //订单编号
        $data['t_order_user_mobile']   =       $mobile;                            //联系人手机号
        $data['t_order_user_name']     =       $name;                              //联系人名称
        $data['t_tick_code']            =       $code;                              //门票商品编号
        $data['t_tick_order_type']     =        4;                                  //订单情况 4待付款
        $data['t_tick_create_time']    =        date("Y-m-d H:i:s", time());       //下单时间
        $data['t_tick_id']              =       $gysCode;                             //商户id
        $data['t_tick_name']            =       $tickInfo['t_tick_name']."+".$tickInfo['t_tick_cat']."+".$tickInfo['t_tick_spot'];//产品名称
        $data['t_tick_my_price']       =        $myPrice;                           //平台价格
        $data['t_tick_num']             =       $num;                               //数量
        $data['t_tick_price']           =       $num * $myPrice;                    //订单总金额
        $data['t_tick_mark_price']     =        $markPrice;                         //市场价
        $data['t_tick_js_price']       =        $sePrice;                           //结算价格
        $data['t_jsx_code']             =        $jsxCode;                           //经销商编码
        $data['t_tick_order_rate']     =        $tickInfo['t_tick_rate'];           //费率
        $data['t_order_user_id']       =        $this->user_account;              //下单用户

        $addRes=M('tick_order')->add($data);
        if($addRes){
            $return['orderSn'] = $data['t_order_sn'];
            $return['price'] = $data['t_tick_price'];
            $return['name'] = $tickInfo['t_tick_name'];
            $return['num'] = $data['t_tick_num'];
            $return['date'] = $date;
            $this->ajaxReturn(array('code' => 200, "data" => $return));
        }else{
            $this->ajaxReturn(array('code' => 403, "msg" => "下单失败，稍后再试"));
        }

    }

    /**
     * 酒景订单下单 todo 待测试
     */
    public function addSecenyOrder()
    {
        $postData = I('post.');
        $results = D("Order")->addSecenyValid($postData);
        if ($results["code"] == 0) {
            $this->ajaxReturn(array('code' => 403, "msg" => $results["msg"]));
        }
        $postData = $results['msg'];
        $mobile = $postData['mobile'];                      //联系人手机号
        $name = $postData['name'];                          //联系人名称
        $identification = $postData['identification'];     //联系人身份证
        $code = $postData['gyscode'];                       //商户编码
        $jxsCode = $postData['jxscode'];                    //经销商编码
        $seceneyCode = $postData['code'];                   //套餐编码
        $playInfo = json_encode($postData['info']);         //游玩人信息
        $num = $postData['num'];                            //数量
        $date = strtotime($postData['date']);               //游玩日期
        $result = M('scenery')->where(array('s_code' => $seceneyCode, 's_user_id' => $code))->find();
        if ($result['s_tick_date'] == 1) {
            $cinfo = M('scenery_yx')->where(array('unix_timestamp(y_b_time)' => $date, 'y_code' => $seceneyCode, 'y_user_id' => $code))->find();
            if (empty($cinfo)) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '非法数据'));
            }
            if ($cinfo['y_ck'] < $num) {
                $this->ajaxReturn(array('code' => 304, "msg" => "库存已经不够，目前还剩" . $cinfo['y_ck'] . "人"));
            }
            $data['o_mark_price'] = $cinfo['y_mark_price'];         //市场价格
            $data['o_plane_price'] = $cinfo['y_my_price'];          //平台价格
            $data['o_js_price'] = $cinfo['y_js_price'];             //结算价格

        } elseif ($result['s_tick_date'] == 2) {
            $cinfo = M('seceny_price')->where(array('unix_timestamp(p_date)' => $date, 'p_code' => $seceneyCode, 'p_user_code' => $code))->find();
            if (empty($cinfo)) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '非法数据'));
            }
            if ($cinfo['p_ck'] < $num) {
                $this->ajaxReturn(array('code' => 304, "msg" => "库存已经不够，目前还剩" . ($num) . "人"));
            }
            $data['o_mark_price'] = $cinfo['p_mark_price'];         //市场价格
            $data['o_plane_price'] = $cinfo['p_my_price'];          //平台价格
            $data['o_js_price'] = $cinfo['p_js_price'];             //结算价格
        }
        $data['o_order_sn'] = $this->createOrderSn();                       //订单编号
        $data['o_order_type'] = 4;                                          //订单情况
        $data['o_mobile'] = $mobile;                                        //联系人手机号
        $data['o_identification'] = $identification;                       //联系人身份证
        $data['o_user_id'] = $code;                                         //商户编码
        $data['o_num'] = $num;                                              //购买数量
        $data['o_seceny_code'] = $seceneyCode;                             //套餐编码
        $data['o_name'] = $name;                                            //联系人名字
        $data['o_order_price'] = $data['o_plane_price'] * $num;           //订单价格 = 平台价格 * 数量
        $data['o_order_time'] = date("Y-m-d H:i:s", time());              //订单添加时间
        $data['o_seceny_name'] = $result['s_name'];                        //套餐名称
        $data['o_jxs_code'] = $jxsCode;                                     //经销商编码
        $data['o_order_play_info'] = $playInfo;                             //游玩人信息
        $data['o_order_add_user'] = $this->user_account;                   //用户手机号
        $data['o_date'] = date("Y-m-d", $date);                             //出游时间
        $data['o_rate'] = $result['s_rate'];                                //佣金比例
        $data['o_tick_id'] = $result['s_view'];                             //景点编码
        $data['o_food_id'] = $result['s_food'];                             //餐饮编码
        $addResult = M('seceny_order')->add($data);
        if (!$addResult) {
            $this->ajaxReturn(array('code' => 403 , "msg" => "下单失败请稍后再试"));
        } else {
            $this->ajaxReturn(array('code' => 0 , "data" => $data));
        }
    }

    /**
     * 跟团游订单详情显示
     */
    private function getOrderGroupDetails($orderSn)
    {
        $orderInfo = M('group_order')->where(array('g_order_sn' => $orderSn))->find();
        if (!$orderInfo) {
            $this->ajaxReturn(array('code' => 304, "msg" => "订单查询出错"));
        } else {
            if ($orderInfo["g_identity_info"]) {
                $orderInfo["g_identity_info"] = json_decode($orderInfo["g_identity_info"], true);
            }
            $group = M("group")->field("g_file")->where("g_code =" . $orderInfo["g_group_code"])->limit(1)->select();
            if (!$group) {
                $this->ajaxReturn(array('code' => 304, "msg" => "产品信息获取错误"));
            }

            $this->GroupHeadImg($group, "g_file");    // 处理首图;
            $orderInfo["img"] = $group[0]["imgFile"];
//            var_dump($orderInfo);
            $this->ajaxReturn(array('code' => 200, 'data' => $orderInfo));
        }
    }

    /**
     * 门票订单详情显示
     */
    private function getTickOrderDetail($orderSn)
    {
        $orderInfo = M('tick_order')->where(array('t_order_sn' => $orderSn))->find();
        if (!$orderInfo) {
            $this->ajaxReturn(array('code' => 304, "msg" => "订单查询出错"));
        }
        $orderInfo['t_play_info'] = json_decode($orderInfo['t_play_info'], true);
        $tickInfo = M('tick')->field('t_tick_file')->where(array('t_code' => $orderInfo['t_tick_code'], 't_user_id' => $orderInfo['t_tick_id']))->limit(1)->select();
        if (!$tickInfo) {
            $this->ajaxReturn(array('code' => 304, "msg" => "订单详细出错"));
        }
        $this->HeadImg($tickInfo,"t_tick_file");    // 处理首图
        $orderInfo["img"] = $tickInfo[0]["imgFile"];
        $this->ajaxReturn(array('code' => 200, 'data' => $orderInfo));
    }

    /**
     * 景酒套餐订单详情显示
     */
    private function getSecenyOrderDetail($orderSn)
    {
        $orderInfo = M('seceny_order')->where(array('o_order_sn' => $orderSn))->find();
        if (empty($orderInfo)) {
            $this->ajaxReturn(array('code' => 304, "msg" => "订单查询出错"));
        }
        $sceneryInfo = M('scenery')->field("s_img")->where(array('s_code' => $orderInfo['o_seceny_code'], 's_user_id' => $orderInfo['o_user_id']))->limit(1)->select();
        if (!$sceneryInfo) {
            $this->ajaxReturn(array('code' => 304, "msg" => "产品信息获取错误"));
        }
        $this->GroupHeadImg($sceneryInfo,"s_img");    // 处理首图
        $orderInfo['img'] = $sceneryInfo[0]["imgFile"];

        $this->ajaxReturn(array('code' => 200, 'data' => $orderInfo));
    }

    // 处理首图
    public function GroupHeadImg(&$list, $name)
    {
        foreach ($list as &$val) {
            $img = json_decode($val[$name], true);
            foreach ($img as $i) {
                if ($i['headImg'] === 'true') {
                    $val['imgFile'] = C('img_url') . $i['src'];
                    break;
                }
            }
            if (empty($val['imgFile'])) {
                $val['imgFile'] = C('img_url') . $img[0]['src'];
            }
            unset($val[$name]);
        }
        return $list;
    }

    // 处理首图
    public function HeadImg(&$list,$name){
        foreach ($list as &$val) {
            $img = json_decode($val[$name], true);
            foreach ($img as $i) {
                if ($i['head'] === 'true') {
                    $val['imgFile'] = C('img_url') . $i['src'];
                    break;
                }
            }
            if (empty($val['imgFile'])) {
                $val['imgFile'] = C('img_url') . $img[0]['src'];
            }
            unset($val[$name]);
        }
        return $list;
    }

    //生成订单编号
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

}