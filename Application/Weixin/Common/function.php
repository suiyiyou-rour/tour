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

/**
 * 图片合成 左上方
 * @param string $bigImagePath   大图地址
 * @param string $qrCodePath     小图地址 
 * @param string $savePath       保存地址
 * @param int    $left           距离左边的位置
 * @param int    $top            距离上方的位置
 */

function qCode($bigImagePath,$qrCodePath,$savePath,$left=0,$top=0){
    vendor('Image.class#qCode');
    $img = new newImage();
    $img->qCode($bigImagePath,$qrCodePath,$savePath,$left,$top);
}

/**
 * 图片合成 右下方
 * @param string $bigImagePath   大图地址
 * @param string $qrCodePath     小图地址 
 * @param string $savePath       保存地址
 * @param int    $right          距离右边的位置
 * @param int    $bottom         距离下方的位置
 */
function lowerRight($bigImagePath,$qrCodePath,$savePath,$right=0,$bottom=0){
    vendor('Image.class#qCode');
    $img = new newImage();
    $img->lowerRight($bigImagePath,$qrCodePath,$savePath,$right,$bottom);
}

/**
 * 缩略图
 * @param string $imagePath      图片地址
 * @param string $savePath       保存地址
 * @param int    $width          缩略宽度
 * @param int    $hight          缩略长度
 */

function thumb($imagePath,$savePath,$width=0,$hight=0){
    vendor('Image.class#qCode');
    $img = new newImage();
    $img->thumb($imagePath,$savePath,$width,$hight);
}

/**
 * 字符串截取通用函数
 * @param  string  $str     要截取的字符串
 * @param  integer $start   截取开始位置
 * @param  integer  $length  截取长度
 * @param  string  $charset 字符串编码格式
 * @param  boolean $suffix  是否显示省略符号
 * @return string           截取后的字符串
 */
function msubstr($str, $start = 0, $length, $charset = "utf-8", $suffix = false) {
    if (function_exists("mb_substr")) {
        if ($suffix)
            return mb_substr($str, $start, $length, $charset) . "...";
        else
            return mb_substr($str, $start, $length, $charset);
    } elseif (function_exists('iconv_substr')) {
        if ($suffix)
            return iconv_substr($str, $start, $length, $charset) . "...";
        else
            return iconv_substr($str, $start, $length, $charset);
    }
    $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
    $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
    $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
    $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("", array_slice($match[0], $start, $length));
    if ($suffix)
        return $slice . "…";
    return $slice;
}


