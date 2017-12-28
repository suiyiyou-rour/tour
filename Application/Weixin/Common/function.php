<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/18
 * Time: 10:56
 */

/**
 * 检测是否是手机访问
 */
function is_mobile_access(){
    $useragent=isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    $useragent_commentsblock=preg_match('|\(.*?\)|',$useragent,$matches)>0?$matches[0]:'';
    function _is_mobile($substrs,$text){
        foreach($substrs as $substr)
            if(false!==strpos($text,$substr)){
                return true;
            }
        return false;
    }
    $mobile_os_list=array('Google Wireless Transcoder','Windows CE','WindowsCE','Symbian','Android','armv6l','armv5','Mobile','CentOS','mowser','AvantGo','Opera Mobi','J2ME/MIDP','Smartphone','Go.Web','Palm','iPAQ');
    $mobile_token_list=array('Profile/MIDP','Configuration/CLDC-','160×160','176×220','240×240','240×320','320×240','UP.Browser','UP.Link','SymbianOS','PalmOS','PocketPC','SonyEricsson','Nokia','BlackBerry','Vodafone','BenQ','Novarra-Vision','Iris','NetFront','HTC_','Xda_','SAMSUNG-SGH','Wapaka','DoCoMo','iPhone','iPod');

    $found_mobile=_is_mobile($mobile_os_list,$useragent_commentsblock) ||
        _is_mobile($mobile_token_list,$useragent);
    if ($found_mobile){
        return true;
    }else{
        return false;
    }
}

/**
 * 身份证判断
 */
function is_Identification_card($identity){
    if(preg_match('/^[1-9]{1}[0-9]{14}$|^[1-9]{1}[0-9]{16}([0-9]|[xX])$/',$identity)){
        return true;
    }
    return false;
}

/**
 * 手机判断/^1\d{10}$/
 */
function is_phone($phone){
    if(preg_match('/^1\d{10}$/',$phone)){
        return true;
    }
    return false;
}


/**
 * 判断是不是日期
 */
function is_Date($str,$format="Y-m-d"){
    $unixTime_1 = strtotime($str);
    if ( !is_numeric($unixTime_1) ) return 0;
    $checkDate = date($format, $unixTime_1);
    $unixTime_2 = strtotime($checkDate);
    if($unixTime_1 == $unixTime_2){
        return true;
    }else{
        return false;
    }
}

/**
 * 判断是不是数字
 */

function is_Num($str){
    if(is_numeric($str)){

        return true;
    }
    return false;
}


/**
 * 功能：生成二维码
 * @param string $qr_data     手机扫描后要跳转的网址
 * @param string $qr_level    默认纠错比例 分为L、M、Q、H四个等级，H代表最高纠错能力
 * @param string $qr_size     二维码图大小，1－10可选，数字越大图片尺寸越大
 * @param string $save_path   图片存储路径
 * @param string $save_prefix 图片名称前缀
 */
function createQRcode($save_path,$qr_data='PHP QR Code :)',$qr_level='L',$qr_size=4,$save_prefix='qrcode'){
    if(!isset($save_path)) return '';
    //设置生成png图片的路径
    $PNG_TEMP_DIR = & $save_path;
    //导入二维码核心程序
    vendor('PHPQRcode.class#phpqrcode');
    //检测并创建生成文件夹
    if (!file_exists($PNG_TEMP_DIR)){
        mkdir($PNG_TEMP_DIR);
    }
    $filename = $PNG_TEMP_DIR.'test.png';
    $errorCorrectionLevel = 'L';
    if (isset($qr_level) && in_array($qr_level, array('L','M','Q','H'))){
        $errorCorrectionLevel = & $qr_level;
    }
    $matrixPointSize = 4;
    if (isset($qr_size)){
        $matrixPointSize = & min(max((int)$qr_size, 1), 10);
    }
   
    if (isset($qr_data)) {
        if (trim($qr_data) == ''){
            die('data cannot be empty!');
        }
        //生成文件名 文件路径+图片名字前缀+md5(名称)+.png
        $filename = $PNG_TEMP_DIR.$save_prefix.md5($qr_data.'|'.$errorCorrectionLevel.'|'.$matrixPointSize).'.png';
        //开始生成
        QRcode::png($qr_data, $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    } else {
        //默认生成
        QRcode::png('PHP QR Code :)', $filename, $errorCorrectionLevel, $matrixPointSize, 2);
    }
    if(file_exists($PNG_TEMP_DIR.basename($filename)))
        return basename($filename);
    else
        return FALSE;
}