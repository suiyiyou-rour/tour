<?php
/**
 * Created by zhang.
 * User: zhang
 * Date: 2017/11/13
 * Time: 15:54
 */

namespace Page\Controller;

use Think\Controller;

class SmsController extends Controller
{

    // 保存错误信息
    public $error;
    // Access Key ID
    private $accessKeyId = '';
    // Access Access Key Secret
    private $accessKeySecret = '';
    // 签名
    private $signName = '';
    // 模版ID
    private $templateCode = '';

    public function __construct($cofig = array())
    {
        $cofig = array(
            'accessKeyId' => 'LTAI4tfw5qzG499J',
            'accessKeySecret' => 'F3zIzdabHuUpVGH6XVBLg8EvUyWB89',
            'signName' => '随意游网络',
            'templateCode' => 'SMS_109705432'
        );
        // 配置参数
        $this->accessKeyId = $cofig ['accessKeyId'];
        $this->accessKeySecret = $cofig ['accessKeySecret'];
        $this->signName = $cofig ['signName'];
        $this->templateCode = $cofig ['templateCode'];
    }

    private function percentEncode($string)
    {
        $string = urlencode($string);
        $string = preg_replace('/\+/', '%20', $string);
        $string = preg_replace('/\*/', '%2A', $string);
        $string = preg_replace('/%7E/', '~', $string);
        return $string;
    }

    /**
     * 签名
     *
     * @param unknown $parameters
     * @param unknown $accessKeySecret
     * @return string
     */
    private function computeSignature($parameters, $accessKeySecret)
    {
        ksort($parameters);
        $canonicalizedQueryString = '';
        foreach ($parameters as $key => $value) {
            $canonicalizedQueryString .= '&' . $this->percentEncode($key) . '=' . $this->percentEncode($value);
        }
        $stringToSign = 'GET&%2F&' . $this->percentencode(substr($canonicalizedQueryString, 1));
        $signature = base64_encode(hash_hmac('sha1', $stringToSign, $accessKeySecret . '&', true));
        return $signature;
    }

    /**
     * 发送验证码 https://help.aliyun.com/document_detail/44364.html?spm=5176.doc44368.6.126.gSngXV
     *
     * @param unknown $mobile
     * @param unknown $verify_code
     *
     */
    private function sendVerify($mobile, $verify_code)
    {
        $params = array(
            // 公共参数
            'SignName' => $this->signName,
            'Format' => 'JSON',
            'Version' => '2017-05-25',
            'AccessKeyId' => $this->accessKeyId,
            'SignatureVersion' => '1.0',
            'SignatureMethod' => 'HMAC-SHA1',
            'SignatureNonce' => uniqid(),
            'Timestamp' => gmdate('Y-m-d\TH:i:s\Z'),
            // 接口参数
            'Action' => 'SendSms',
            'TemplateCode' => $this->templateCode,
            'PhoneNumbers' => $mobile,
            'TemplateParam' => '{"code":"' . $verify_code . '"}'
        );
        // 计算签名并把签名结果加入请求参数
        $params ['Signature'] = $this->computeSignature($params, $this->accessKeySecret);
        // 发送请求
        $url = 'https://dysmsapi.aliyuncs.com/?' . http_build_query($params);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        $result = curl_exec($ch);
        curl_close($ch);
        $result = json_decode($result, true);
        if ($result['Code'] == 'OK') {
            return true;
        } else {
            return false;
        }

    }

    /**
     * 获取详细错误信息
     *
     * @param unknown $status
     */
    private function getErrorMessage($status)
    {
        // https://api.alidayu.com/doc2/apiDetail?spm=a3142.7629140.1.19.SmdYoA&apiId=25450
        $message = array(
            'InvalidDayuStatus.Malformed' => '账户短信开通状态不正确',
            'InvalidSignName.Malformed' => '短信签名不正确或签名状态不正确',
            'InvalidTemplateCode.MalFormed' => '短信模板Code不正确或者模板状态不正确',
            'InvalidRecNum.Malformed' => '目标手机号不正确，单次发送数量不能超过100',
            'InvalidParamString.MalFormed' => '短信模板中变量不是json格式',
            'InvalidParamStringTemplate.Malformed' => '短信模板中变量与模板内容不匹配',
            'InvalidSendSms' => '触发业务流控',
            'InvalidDayu.Malformed' => '变量不能是url，可以将变量固化在模板中'
        );
        if (isset ($message [$status])) {
            return $message [$status];
        }
        return $status;
    }

    //获取短信验证码 
    public function useSendSms()
    {
        $mobile = I('mobile');
        
        // 手机号码验证
        if(!preg_match('/^1\d{10}$/',$mobile)){
            $this->ajaxReturn(array('code' => 401, 'msg' => '手机号码不正确'));
        }
        $isSend = M('sms_code')->where(array('c_mobile' => $mobile))->find();

        // 不为空的情况
        //     先判断次数是否超过
        //         未超过 -- 再判断时间是否超过
        if(!empty($isSend)){
            if ((int)$isSend['c_type'] > 3) {
                $this->ajaxReturn(array('code' => 402, 'msg' => '当日次数已达上限'));
            }elseif($isSend['c_time'] + 60 > time()){
                $this->ajaxReturn(array('code' => 403, 'msg' => '不能重复发送'));
            }
            $requestNum = (int)$isSend['c_type'];
        }else{
            $requestNum = 0;
        }                                                                                                          
        
        // 获取 $code
        $code = $this->generate_code();
        $result = $this->sendVerify($mobile, $code);
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

    // 随机生成6位数验证码 pow() -> 求幂
    private function generate_code($length = 6)
    {
        return rand(100000, 999999);
    }
}