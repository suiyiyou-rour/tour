<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2014 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 麦当苗儿 <zuojiazi@vip.qq.com> <http://www.zjzit.cn>
// +----------------------------------------------------------------------
/*
 * token存储
 * 日  期：201708011
 */
namespace Org\Custom;
class Token {
    public function gettoken(){
        $appid=C("APP_ID");
        $secret=C("APP_SECRET");
        date_default_timezone_set('PRC');
        $nowtime = date("Y-m-d H:i:s");
        $bol=1;
        $result = M('app_token')->limit(1)->select();
        if(!$result){
            $bol=2;
        }else{
            if(strtotime($nowtime)>=strtotime($result[0]["last_time"])){
                $bol=3;
            }
        }
        if($bol==1){
            return $result[0]["token"];
        }else{
            $curl = curl_init();
            curl_setopt ($curl, CURLOPT_URL, 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$appid.'&secret='.$secret);
            curl_setopt ($curl, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE);
            $re = curl_exec($curl);
            curl_close($curl);
            $eq= json_decode($re,true);
            if($eq["access_token"] && $eq["expires_in"]){
                $data["token"] = $eq["access_token"];
                $data["last_time"] = date('Y-m-d H:i:s',strtotime($nowtime)+$eq["expires_in"]);
                if($bol==2){
                    $result2=M('app_token')->data($data)->add();
                }else if($bol==3){
                    $result2=M('app_token')->where("id = '".$result[0]["id"]."'")->data($data)->save();
                }
                if($result2){
                    return $data["token"];
                }
            }
        }
    }
}