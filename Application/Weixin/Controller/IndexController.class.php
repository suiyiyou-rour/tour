<?php
/**
 *  页面以及 列表接口
 */
namespace Weixin\Controller;
class IndexController extends BaseController {

    public function __construct(){
        parent::__construct();
        // 分销商
        $pid = I('get.pid');// 分销商id
        $this->pid($pid);

        //分享
        $wx = D('WeiXinApi','Service');
        $str = $wx->JSSDK();
        $this->assign("wxconfig",$str);

//        $actionName = ACTION_NAME; // 当前操作名
//        $actionArr = array(
//            'register','home','p_route',
//            's_ticket','s_route','s_hotel',
//            'd_route','route_pay','ticket_pay',
//            'order','p_ticket','d_ticket',
//            'route_detail','login'
//        );
//        $inRes = in_array($actionName,$actionArr);
//        if($inRes){

//        }
    }

    // 分销商
    public function pid($pid){
        //$pid = I('get.pid');
        $openid = session('openid');
        $jsPid = cookie('pid');
        if($pid && !$jsPid){// pid 有 cookie 无 查表
            $pidData = M('user')->where('user_wx_code="'.$openid.'"')->find();
            // 无经销商写入  有直接查经销商

            if($pidData['user_is_lx'] == null){
                $saveData['user_is_lx'] = $pid;
                M('user')->where('user_wx_code="'.$openid.'"')->save($saveData);
            }
            $cookieData =  M('user')->field('user_company company,user_id pid,user_head_img img,user_account phone')->where('user_id='.$pid.' and user_type = 2')->find();
            if($cookieData){
                $company = $cookieData['company'];
                $newPid = $cookieData['pid'];
                $img = $cookieData['img'];
                $phone = $cookieData['phone'];
                cookie('company',$company);
                cookie('pid',$newPid);
                cookie('img',$img);
                cookie('phone',$phone);
            }
        }elseif($pid && $jsPid){ // pid 有 cookie 有  不相等 直接查pid
            if($pid != $jsPid){
                $cookieData =  M('user')->field('user_company company,user_id pid,user_head_img img,user_account phone')->where('user_id='.$pid.' and user_type = 2')->find();
                if($cookieData){
                    $company = $cookieData['company'];
                    $newPid = $cookieData['pid'];
                    $img = $cookieData['img'];
                    $phone = $cookieData['phone'];
                    cookie('company',$company);
                    cookie('pid',$newPid);
                    cookie('img',$img);
                    cookie('phone',$phone);
                }
            }

        }elseif(!$pid && !$jsPid){ // pid 无 cookie 无
            // 查表 查看是否有 对应经销商
            $pidData = M('user')->where('user_wx_code="'.$openid.'"')->find();
            if($pidData['user_is_lx'] != null){
                $pid = $pidData['user_is_lx'];
                $cookieData =  M('user')->field('user_company company,user_id pid,user_head_img img,user_account phone')->where('user_id='.$pid.' and user_type = 2')->find();
                if($cookieData){
                    $company = $cookieData['company'];
                    $newPid = $cookieData['pid'];
                    $img = $cookieData['img'];
                    $phone = $cookieData['phone'];
                    cookie('company',$company);
                    cookie('pid',$newPid);
                    cookie('img',$img);
                    cookie('phone',$phone);
                }
            }

        }
    }

    public function test(){

        session('openid',null);
        session('online_use_info',null);
        cookie('company',null);
        cookie('pid',null);
        cookie('img',null);
        cookie('phone',null);
    }

    //首页
    public function index(){
        $this->display('index/home');
    }

    //首页
    public function home(){
//        $wx = D('WeiXinApi','Service');
//        $str = $wx->JSSDK();
//        $this->assign("wxconfig",$str);
//        echo $str;exit;
        $this->display('index/home');
    }

    //注册
    public function register(){
        $this->display('index/register');
    }

    //跟团游详情
    public function p_route(){
        $this->display('index/p_route');
    }

    //门票搜索
    public function s_ticket(){
        //判断是不是分销商自己
        $jsremark = $this->checkJS();
        if($jsremark){
            $this->assign("jsremark","1");
        }else{
            $this->assign("jsremark","0");
        }
        $this->display('index/s_ticket');
    }

    //跟团游搜索
    public function s_route(){
        //判断是不是分销商自己
        $jsremark = $this->checkJS();
        if($jsremark){
            $this->assign("jsremark","1");
        }else{
            $this->assign("jsremark","0");
        }
        $this->display('index/s_route');
    }

    //酒店搜索
    public function s_hotel(){
        $this->display('index/s_hotel');
    }

    //跟团游 游客信息
    public function d_route(){
        $this->display('index/d_route');
    }

    //跟团游支付
    public function route_pay(){
        $this->display('index/route_pay');
    }

    public function ticket_pay(){
        $this->display('index/ticket_pay');
    }

    //订单管理
    public function order(){
        $userCode = $_SESSION["online_use_info"]["user_account"]; // 用户code
        if($userCode){
            $this->display('index/order');
        }else{
            $this->display('index/login');
        }


    }

    public function p_ticket(){
        $this->display('index/p_ticket');
    }

    public function d_ticket(){
        $this->display('index/d_ticket');
    }
    // 详情
    public function route_detail(){
        $this->display('index/route_detail');
    }

    public function login(){
        $this->display('index/login');
    }

    public function ticket_detail(){
        $this->display("index/ticket_detail");
    }

    //提现
    public function cash(){
        $this->display("index/cash");
    }

    //建设中 网页
    public function laterOn(){
        $str = C("APP_URL").__APP__."/Weixin/index/home";
        $this->assign("str","功能还在开发中");
        $this->assign("url",$str);
        $this->display("common/errFour");
    }

    public function p_hotel(){
        $this->display("index/p_hotel");
    }

    public function d_hotel(){
        $this->display("index/d_hotel");
    }

    public function hotel_pay(){
        $this->display("index/hotel_pay");
    }
    
    public function share_poster(){
        $this->display("index/share_poster");
    }

    public function share_info(){
        $this->display("index/share_info");
    }

    //微信按钮判断登陆
    public function checkLogin(){
        $account = $_SESSION["online_use_info"]["user_account"];
        $js = $this->checkJS();
        if (!$account || !$js) {        //没有登陆或者经销商不是自己
            $this->display('index/login');
        }else{
            $this->display('index/home');

        }
    }

    //判断自己是不是经销商
    private function checkJS(){
        $openid = session('openid');
        $jsPid = cookie('pid');
        $jsremark = false;
        if($openid && $jsPid){
            $jsarray["user_id"] = $jsPid;
            $jsarray["user_is_lx"] = $jsPid;
            $jsarray["user_wx_code"] = $openid;
            $jsarray["user_type"] = 2;
            $jsre=M('user')->field('user_id')->where($jsarray)->find();
            if($jsre){
                $jsremark = true;
            }
        }
        return $jsremark;
    }
}
