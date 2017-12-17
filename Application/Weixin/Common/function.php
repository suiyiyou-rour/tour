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
