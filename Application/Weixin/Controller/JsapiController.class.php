<?php
/**
 * Created by PhpStorm.
 * User: Stone
 * Date: 2016/9/19
 * Time: 00:12
 * mail: wxstones@gmail.com
 */

namespace Weixin\Controller;

use Think\Controller;

class JsapiController extends Controller
{

    /** 链接wechat接口 */
    public function index()
    {
        header("Content-Type:text/html;charset=utf-8");
        $user_account = $_SESSION["online_use_info"]["user_account"];
//         $this->user_account="18060481803";        //测试
        $need_type = I('shopType');//商品类型
        $need_orderSn = I('orderSn');//订单号
//        $need_type ="group";
//        $need_orderSn = "w20171123185916110746232";
        if (!$need_type || !$need_orderSn || !$user_account) {
            $this->assign("str","支付页面发生错误");
            $this->assign("url",C("APP_URL").__APP__."/Weixin/index/home");
            $this->display("common/errFour");
            exit;
        }
        //数据检验 获取数据 OrderModel
        $res = D("order")->getOrderInfo($need_type, $need_orderSn);
        if ($res["code"] == 0) {
            $this->assign("str","订单查询发生错误");
            $this->assign("url",C("APP_URL").__APP__."/Weixin/index/home");
            $this->display("common/errFour");
            exit;
        }
        $orderName = $res["msg"]["name"];                                    // 商品名称
        $orderPrice = $res["msg"]["order_price"];                            //商品总价格 (分)
        $orderSn = $res["msg"]["order_sn"];                                  //订单编号
        $orderAttach = $res["msg"]["attach"];                                 //自定义数据回调里面用到  group
        $orderGoods_tag = $res["msg"]["Goods_tag"];                           // 商品标记 （好像没什么用）随意游-跟团游

//        $orderName = "asdsadsadsa";
//        $orderSn = "12312312312312344";
//        $orderAttach = "123123";
//        $orderPrice = "1";
//        $orderGoods_tag = "1232132";
        $tal_fee = $orderPrice * 100; // 支付价格

        //支付必须文件
        Vendor('Wxpay.WxPay#Api');
        Vendor('Wxpay.WxPay#JsApiPay');

        //①、获取用户openid
        $tools = new \JsApiPay();
        $openId = $tools->GetOpenid();

        //②、统一下单
        $input = new \WxPayUnifiedOrder();
        $input->SetBody($orderName);//设置商品或支付单简要描述
        $input->SetAttach($orderAttach);//商户携带订单的自定义数据

        $input->SetOut_trade_no($orderSn);//设置商户系统内部的订单号,32个字符内、可包含字母, 其他说明见商户订单号

        $input->SetTotal_fee($tal_fee);//订单总金额，只能为整数
        $input->SetTime_start(date("YmdHis"));//设置订单生成时间，格式为yyyyMMddHHmmss，如2009年12月25日9点10分10秒表示为20091225091010。其他详见时间规则
        $input->SetTime_expire(date("YmdHis", time() + 600));//设置订单失效时间
        $input->SetGoods_tag($orderGoods_tag);//设置商品标记

        $input->SetNotify_url("http://www.suiyiyou.net/index.php/Weixin/Jsapi/notify_url");//设置接收微信支付异步通知回调地址
        $input->SetTrade_type("JSAPI");//

        $input->SetOpenid($openId);
        $order = \WxPayApi::unifiedOrder($input);

        $jsApiParameters = $tools->GetJsApiParameters($order);

        $this->assign('jsApiParameters', $jsApiParameters);//配置参数
        if($need_type=="group"){
            $this->display("index/route_pay");
        }else if($need_type=="tick"){
            $this->display("index/ticket_pay");
        }else if($need_type=="scenery"){
            $this->display("index/hotal_pay");
        }
    }

    /* 支付回调地址 只要有数据回来  那说明   肯定是支付成功的   不支付成功不会回调*/
    public function notify_url()
    {
        $xml = $GLOBALS['HTTP_RAW_POST_DATA']; //返回的xml
        if(empty($xml)){
            return;
        }
        libxml_disable_entity_loader(true);
        $postObj = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)));
//        $postStr = file_get_contents("php://input");
//        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        $outputdata["order_sn"]         =       $postObj->out_trade_no;        //订单号
        $outputdata["openid"]           =       $postObj->openid;               //openid
        $outputdata["price"]            =       ($postObj->total_fee) / 100;    //价格
        $outputdata["mark"]             =       $postObj->attach;               //标记
        $outputdata["date"]             =       date("Y-m-d H:i:s");           //添加时间
        $outputdata["xml"]              =       $xml;                           //微信返回的整个xml
        if(empty($outputdata["order_sn"])){
            return;
        }
        //记录添加
        $record = M('pay_record')->data($outputdata)->add();
        if(!$record){
            return;
        }
        //数据分类型
        if ($outputdata["mark"] == "group") {
            $result = D("Order", "Logic")->updateGroupOrder($outputdata["order_sn"]);
            //code 0 订单异常 ;1 正常; 2 订单处理正常,销量库存处理异常
            if($result["code"] != 0){
                $this->groupNote($outputdata["order_sn"],$result["num"]);
            }
        } else if ($outputdata["mark"] == "tick") {
            $result = D("Order", "Logic")->updateTickOrder($outputdata["order_sn"]);
            if($result["code"] != 0){
                $this->tickNote($outputdata["order_sn"],$result["num"]);
            }
        } else if ($outputdata["mark"] == "scenery") {
            //todo 要测试
            $result = D("Order", "Logic")->updateSceneryOrder($outputdata["order_sn"]);
        }
        if($result["code"] == 1){
            $prwhere["bool"] = "1";
        }else if($result["code"] == 2){
            $prwhere["bool"] = "2";
        }else{
            $prwhere["bool"] = "0";
        }
        $prwhere["error_info"] = $result["msg"];

        M('pay_record')->data($prwhere)->where(array("id" => $record))->save();



        /* 如果不回复这个xml  微信会给我们发送三次xml */
        $su = '<xml> <return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>';
////        echo 'SUCCESS';
        echo $su;
        exit;
    }

    private function groupNote($orderSn,$num){//发送短信通知供应商
        //供应商短信通知
        $payStatus = M('group_order')->field("g_user_id,g_group_name,g_name,g_pay_time")->where('g_order_sn=' . $orderSn)->find();
        if($payStatus["g_user_id"]){
            $pSwhere["sp_id"] = $payStatus["g_user_id"];
            $pSwhere["sp_open"] = 1;
            $paySp = M('sp')->field("sp_mobile")->where($pSwhere)->find();
            if($paySp["sp_mobile"]){
                $WxSms = new \Weixin\Controller\SmsController();
                $WxSms->SmsTo($paySp["sp_mobile"],$payStatus["g_group_name"],$payStatus["g_name"]."，共".$num."人",$payStatus["g_pay_time"]);
            }
        }
    }

    private function tickNote($orderSn,$num){//发送短信通知供应商
        //供应商短信通知
        $orderInfo = M('tick_order')->field("t_tick_id,t_tick_name,t_order_user_name,t_pay_time")->where(array('t_order_sn' => $orderSn))->find();
         if($orderInfo["t_tick_id"]){
                $pSwhere["sp_id"] = $orderInfo["t_tick_id"];
                $pSwhere["sp_open"] = 1;
                $paySp = M('sp')->field("sp_mobile")->where($pSwhere)->find();
                if($paySp["sp_mobile"]){
                    $WxSms = new \Weixin\Controller\SmsController();
                    $WxSms->SmsTo($paySp["sp_mobile"],$orderInfo["t_tick_name"],$orderInfo["t_order_user_name"]."，共".$num."人",$orderInfo["t_pay_time"]);
                }
            }
    }

}