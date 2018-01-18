<?php

namespace Weixin\Controller;
use Think\Controller;

class SmsController extends Controller
{
    
    // 保存错误信息
    public $error;
    // Access Key ID
    private $accessKeyId = '';
    // Access Access Key Secret
    private $accessKeySecret = '';

    public function __construct($cofig = array())
    {        
        $cofig = array(
            'accessKeyId' => 'LTAI4tfw5qzG499J',
            'accessKeySecret' => 'F3zIzdabHuUpVGH6XVBLg8EvUyWB89'
        );
       
        // 配置参数
        $this->accessKeyId = $cofig ['accessKeyId'];
        $this->accessKeySecret = $cofig ['accessKeySecret'];
    }

     /**
     * 生成签名并发起请求
     *
     * @param $accessKeyId string AccessKeyId (https://ak-console.aliyun.com/)
     * @param $accessKeySecret string AccessKeySecret
     * @param $domain string API接口所在域名
     * @param $params array API具体参数
     * @param $security boolean 使用https
     * @return bool|\stdClass 返回API接口调用结果，当发生错误时返回false
     */
    private function request($params, $security=false) {
        $apiParams = array_merge(array (
            "SignatureMethod" => "HMAC-SHA1",
            "SignatureNonce" => uniqid(mt_rand(0,0xffff), true),
            "SignatureVersion" => "1.0",
            "AccessKeyId" => 'LTAI4tfw5qzG499J',
            "Timestamp" => gmdate("Y-m-d\TH:i:s\Z"),
            "Format" => "JSON",
        ), $params);
        ksort($apiParams);
        
        $sortedQueryStringTmp = "";
        foreach ($apiParams as $key => $value) {
            $sortedQueryStringTmp .= "&" . $this->encode($key) . "=" . $this->encode($value);
        }

        $stringToSign = "GET&%2F&" . $this->encode(substr($sortedQueryStringTmp, 1));

        $sign = base64_encode(hash_hmac("sha1", $stringToSign, 'F3zIzdabHuUpVGH6XVBLg8EvUyWB89' . "&",true));

        $signature = $this->encode($sign);

        $url = ($security ? 'https' : 'http')."://dysmsapi.aliyuncs.com/?Signature={$signature}{$sortedQueryStringTmp}";

        try {
            $content = $this->fetchContent($url);
            return json_decode($content);
        } catch( \Exception $e) {
            return false;
        }
    }

    private function encode($str)
    {
        $res = urlencode($str);
        $res = preg_replace("/\+/", "%20", $res);
        $res = preg_replace("/\*/", "%2A", $res);
        $res = preg_replace("/%7E/", "~", $res);
        return $res;
    }

    private function fetchContent($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "x-sdk-client" => "php/2.0.0"
        ));

        if(substr($url, 0,5) == 'https') {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }

        $rtn = curl_exec($ch);

        if($rtn === false) {
            trigger_error("[CURL_" . curl_errno($ch) . "]: " . curl_error($ch), E_USER_ERROR);
        }
        curl_close($ch);

        return $rtn;
    }

    private function sendVerify($params){
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }else{
            return false;
        }
        
        // 请求短信发送接口
        $content = $this->request(
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
        );
        $content = json_decode(json_encode($content),TRUE);
        if($content['Code'] == 'OK'){
            return true;
        }else{
            return false;
        }
    }

    
    //获取短信验证码 
    public function useSendSms()
    {
        $mobile = I('mobile');
        //手机号码验证
        if(!is_phone($mobile)){
            $this->ajaxReturn(array('code' => 404, 'msg' => '手机号码不正确'));
        }
        $isSend = M('sms_code')->where(array('c_mobile' => $mobile))->find();

        // 不为空的情况
        //     先判断次数是否超过
        //         未超过 -- 再判断时间是否超过
        if(!empty($isSend)){
            if ((int)$isSend['c_type'] > 3) {
                $this->ajaxReturn(array('code' => 404, 'msg' => '当日次数已达上限'));
            }elseif($isSend['c_time'] + 60 > time()){
                $this->ajaxReturn(array('code' => 404, 'msg' => '不能重复发送'));
            }
            $requestNum = (int)$isSend['c_type'];
        }else{
            $requestNum = 0;
        }                                                                                                          

        // 配置阿里云短信 -- 模板参数
        $code = $this->generate_code();

        $params["PhoneNumbers"] =  $mobile;
        $params["SignName"] = "随意游网络";
        $params["TemplateCode"] = "SMS_109705432";
        $params['TemplateParam'] = Array (
            "code" => $code
        );
        
        $result = $this->sendVerify($params);

        if ($result) {
            $data['c_code']   =  $code;
            $data['c_mobile'] =  $mobile;
            $data['c_time']   =  time();
            $data['c_type']   =  $requestNum + 1;
            M('sms_code')     -> where(array('c_mobile' => $mobile))->delete();
            $res = M('sms_code')->add($data);
            $this->ajaxReturn(array('code' => 200, 'msg' => '发送成功'));
        } else {
            $this->ajaxReturn(array('code' => 404, 'msg' => '发送失败'));
        }
    }

    /** 用户购买发送给供应商 短信验证码 
     *  @param mobile：供应商手机号
     *  @param productName：产品名
     *  @param userName：用户名
     *  @param time：下单时间
     */ 
    public function SmsTo($mobile,$productName,$userName,$time)
    {                                                                                             
        // 参数校验
        if(!$mobile || !$productName || !$userName || !$time){
            return 0;
        }

        // 配置阿里云短信 -- 模板参数
        $params["PhoneNumbers"] =  $mobile;
        $params["SignName"] = "随意游网络";
        $params["TemplateCode"] = "SMS_112470379";
        $params['TemplateParam'] = Array (
            "productName" => $productName,
            "userName" => $userName,
            "time" => $time,
        );

        $result = $this->sendVerify($params);
        if($result){
            return 1;
        }else{
            return 2;
        }
    }

    // 随机生成6位数验证码 pow() -> 求幂
    public function generate_code($length = 6)
    {
        return rand(100000, 999999);
    }
}