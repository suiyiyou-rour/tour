<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/11/18
 * Time: 10:56
 */

/**
 * curl访问
 * @param string  $url  url连接
 * @param json数组
 * @param headers字符串
 */
function curl_post($url,$data,$headers){
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
    if (!empty($data)){
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS,$data);
    }
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
    if($headers){
        curl_setopt( $curl, CURLOPT_HTTPHEADER, $headers );
    }
    $output = curl_exec($curl);
    curl_close($curl);
    return $output;
}

/**
 * 随机数
 * @param  number
 */
function randstr($length = 16) {
    $chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
    $str = "";
    for ($i = 0; $i < $length; $i++) {
        $str .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
    }
    return $str;
}

/**
 * 生成二维码
 * @param  string  $url  url连接
 * @param  integer $size 尺寸 纯数字
 */
function qrcode($url,$size=4){
    Vendor('Phpqrcode.phpqrcode');
    QRcode::png($url,false,QR_ECLEVEL_L,$size,2,false,0xFFFFFF,0x000000);
}



