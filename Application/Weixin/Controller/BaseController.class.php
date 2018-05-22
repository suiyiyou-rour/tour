<?php
/**
 * 基础类
 */
namespace Weixin\Controller;
use Think\Controller;
class BaseController extends Controller
{
    public function _initialize()
    {
        header('Location: http://wx.suiyiyou.net/#/home');
//        $this->assign("str","系统升级中");
//        $this->assign("url","#");
//        $this->display("common/errFour");
        die;
//         if(!is_mobile_access()){//检测是否是手机访问
//             $this->display("common/error");
//             exit;
//         }
//         header('Access-Control-Allow-Origin:*');//ajax跨域访问
        header("Content-Type:text/html;charset=utf-8");
        date_default_timezone_set('PRC');
        // @todo 判断Session openid 没有获取 Session["openid"]
        // @todo 判断数据库 openid 没有就写入

          $openid = session('openid');
          if(!$openid){
              $openid = $this ->getOpenId(C("APP_ID"), C("APP_SECRET")); // 获取用户openid
 //             $openid = $this -> getOpenId(C("APP_ID"), C("APP_SECRET"), 1); // 获取用户openid
              $userImg = $this->getImg($openid);
              session('openid',$openid);
          }

 //          openid 查询数据库 没数据存  没数据
          $useInfo = session('online_use_info');
          if(empty($useInfo)){
              $useInfo = M('user') ->where(array('user_wx_code' => $openid)) -> find();
              if(empty($useInfo)){
                  M('user') ->add(array('user_wx_code' => $openid, 'user_head_img' => $userImg));
              }elseif($useInfo['user_account'] != null){
                  session('online_use_info',$useInfo);
              }
          }
    }

    /**
     * 网页授权获取用户信息
     * @param type $appId
     * @param type $appSecret
     * @param type $sure 是否需要用户确认授权
     * @return type
     */

     public function tee()
     { 
        session('openid',null);
        session('online_use_info',null);
     }

     //获取用户头像
    public function getImg($openId){
        header("Content-Type:text/html;charset=utf-8");
//        $tokenclass = new \Org\Custom\AccessToken();
//        $ACCESS_TOKEN=$tokenclass->gettoken();
        $wxService = D('WeiXinApi','Service');
        $ACCESS_TOKEN = $wxService->getAccessToken();

        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$ACCESS_TOKEN&openid=$openId&lang=zh_CN";
        $content = file_get_contents($url);
        $content = json_decode($content,true);
        return $content['headimgurl'];
    }

    //获取用户openid
    function getOpenId($appId, $appSecret, $sure = '') {
        $type = empty($sure) ? 'snsapi_base' : 'snsapi_userinfo';
        $url = 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

        // get code
        if (!isset($_GET['code'])) {
            $redirect = 'https://open.weixin.qq.com/connect/oauth2/authorize?appid=' . $appId . '&redirect_uri=' . urlencode($url) . '&response_type=code&scope=' . $type . '&state='.time().'#wechat_redirect';
//            var_dump($redirect);
//            die;
            header('Location: ' . $redirect);
            exit;
        }
//        echo $_GET['code'];
//        die;
//                var_dump($_GET);
//                die;
        // 判断是否CODE失效，刷新时间设置10秒
        /* 微信code只能用一次，此判断解决刷新页面出现code错误的问题，这里是根据state做判断的，所以上边的授权链接这个参数必须写成时间戳*/
        if(( $_GET['state'] + 10) < time()){
            $url = str_replace('&code=', '&oldcode=', $url);
            $url = str_replace('&state=', '&oldstate=', $url);
//            var_dump($url);
//            die;
            header('Location: ' . $url);

        }

//        var_dump($_GET['code']);
//        die;
        // get openid
        $url = "https://api.weixin.qq.com/sns/oauth2/access_token?appid=" . $appId . "&secret=" . $appSecret . "&code=" . $_GET['code'] . "&grant_type=authorization_code";
        $content = file_get_contents($url);
        $ret = json_decode($content, true);
//        var_dump($content);
//        die;


        if (!isset($ret['openid'])) {
            echo 'get openID is fail';
            exit;
        }
        return $ret['openid'];
    }



}