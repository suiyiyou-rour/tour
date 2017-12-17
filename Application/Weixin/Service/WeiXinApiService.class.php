<?php
/**
 * 2017/12/04 LZL
 */
namespace Weixin\Service;
class WeiXinApiService {
    private $APP_ID;
    private $APP_SECRET;

    public function __construct(){
        $this->APP_ID = C("APP_ID");
        $this->APP_SECRET = C("APP_SECRET");
    }

    /**
     * 获取AccessToken
     */
    public function getAccessToken(){
        $result = S('WXAccessToken');
//        $result = false;
        if($result){
            return $result;
        }else{
            $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$this->APP_ID.'&secret='.$this->APP_SECRET;
            $ACToken = $this->curl_get_contents($url);
            $eq = json_decode($ACToken,true);
            if($eq["access_token"] && $eq["expires_in"]){
                S('WXAccessToken',$eq["access_token"],7150);
                return $eq["access_token"];
            }else{
                return false;
            }
        }
    }

    /**
     * 获取api_ticket
     */
    public function get_jsapi_ticket(){
        $result = S('WXJsapiTicket');
        if($result){
            return $result;
        }else{
//            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=".$AccessToken."&type=wx_card";
            $AccessToken = $this->getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/ticket/getticket?type=jsapi&access_token=$AccessToken";
            $jdk=$this->curl_get_contents($url);
            $eq= json_decode($jdk,true);
//        var_dump($jdk);
//        exit;
            if($eq["ticket"] && $eq["expires_in"]){
                S('WXJsapiTicket',$eq["ticket"],7150);
//                var_dump($eq["ticket"]);
//                exit;
                return $eq["ticket"];
            }else{
                return false;
            }
        }
    }

    /**
     * JSSDK 排序加密
     */
    public function JSSDK(){
        $jsapiTicket = $this->get_jsapi_ticket();

        // 注意 URL 一定要动态获取，不能 hardcode.
        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
        $url = "$protocol$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";

        $timestamp = time();
        $nonceStr = $this->getRandStr(16);

        // 这里参数的顺序要按照 key 值 ASCII 码升序排序
        $string = "jsapi_ticket=$jsapiTicket&noncestr=$nonceStr&timestamp=$timestamp&url=$url";

        $signature = sha1($string);

        $signPackage = array(
//            "debug"     => true,
            "appId"     => $this->APP_ID,
            "nonceStr"  => $nonceStr,
            "timestamp" => $timestamp,
//            "url"       => $url,
            "signature" => $signature
//            "rawString" => $string
        );
        return json_encode($signPackage);


//        $noncestr = $this->getRandStr(16);
//        $jsapi_ticket = $this->get_jsapi_ticket();
//        $timestamp = time();
//        $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
//        $url = $protocol.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
////        $string = 'jsapi_ticket='.$jsapi_ticket.'&noncestr='.$noncestr.'&timestamp='.$timestamp.'&url='.$url;
//        $string = "jsapi_ticket=$jsapi_ticket&noncestr=$noncestr&timestamp=$timestamp&url=$url";
//        $signature = sha1($string);
//
//        $data["debug"] = true;
//        $data["appId"] = $this->APP_ID;
//        $data["timestamp"] = $timestamp;
//        $data["nonceStr"] = $noncestr;
//        $data["signature"] = $signature;

//        $data["jsapi_ticket"] = $jsapi_ticket;
//        $data["url"] = $url;
//        print_r($data);exit;
//        return json_encode($data);

    }

    /**
     * curl get
     */
    public function curl_get_contents($url){
//        $curl = curl_init();
//        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($curl, CURLOPT_TIMEOUT, 500);
//        // 为保证第三方服务器与微信服务器之间数据传输的安全性，所有微信接口采用https方式调用，必须使用下面2行代码打开ssl安全校验。
//        // 如果在部署过程中代码在此处验证失败，请到 http://curl.haxx.se/ca/cacert.pem 下载新的证书判别文件。
//        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, true);
//        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, true);
//        curl_setopt($curl, CURLOPT_URL, $url);
//
//        $res = curl_exec($curl);
//        curl_close($curl);
//        return $res;


        $curl = curl_init();
        curl_setopt ($curl, CURLOPT_URL, $url);
        curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
        $re = curl_exec($curl);
        curl_close($curl);
        return $re;

//        $ch = curl_init();
//        curl_setopt($ch, CURLOPT_URL, $url);
//        curl_setopt($ch, CURLOPT_TIMEOUT, 1);
//        curl_setopt($ch, CURLOPT_MAXREDIRS, 200);
//        curl_setopt($ch, CURLOPT_USERAGENT, _USERAGENT_);
//        curl_setopt($ch, CURLOPT_REFERER, _REFERER_);
//        @curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
//        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
//        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//        $r = curl_exec($ch);
//        curl_close($ch);
//        return $r;
    }

    /**
     * 随机字符串
     */
    function getRandStr($length){
        $str = 'abcdefghijklmnopqrstuvwxyz0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randString = '';
        $len = strlen($str)-1;
        for($i = 0;$i < $length;$i ++){
            $num = mt_rand(0, $len);
            $randString .= $str[$num];
        }
        return $randString;
    }
}
