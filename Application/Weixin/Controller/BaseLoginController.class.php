<?php
/**
 * 登陆注册 暂无
 */
namespace Weixin\Controller;

class BaseLoginController extends BaseController
{
    // 登录判断
    public function logined(){
        $isLogin = session('online_use_info');
        if(empty($isLogin)){
            $this->ajaxReturn(array('code' => 404, 'msg' => '未绑定手机'));
        }
        $this->ajaxReturn(array('code' => 200, 'msg' => '已登录'));
    }

    // 注册
    public function register()
    {
        $mobile = I('mobile');
        $verify = I('verify');
        $pwd = md5(I('password'));
        // 手机号码 不合格
        if(!is_phone($mobile)){
            $this->ajaxReturn(array('code' => 404, 'msg' => '手机号码不正确'));
        }

        // 手机号 已注册
        $res = M('user')->where(array('user_account' => $mobile, 'user_pwd' => $pwd))->find();
        if(!empty($res) && $res['user_wx_code'] != null ){
            $this -> ajaxReturn(array('code' => 403, 'msg' => '该手机号码已被注册！请刷新页面！'));
        }

        // 验证码 失效
        $isSend = M('sms_code')->where(array('c_mobile' => $mobile))->find();
        if($isSend['c_time'] + 60 * 15 < time()){
            $this -> ajaxReturn(array('code' => 404, 'msg' => '验证码已失效，请重新发送。'));
        }

        // 验证码 不正确
        if($isSend['c_code'] != $verify){
            $this -> ajaxReturn(array('code' => 404, 'msg' => '验证码错误！'));
        }

        // 注册 写入数据库
        $openid = session('openid');
        // 获取微信头像
        $imgUrl = $this->getImg(); 
        $data = array(
            'user_account'     => $mobile,
            'user_mobile'      => $mobile,
            'user_wx_code'     => $openid,
            'user_regist_time' => time(),
            'user_type'        => '1',
            'user_pwd'         => $pwd,
            'user_head_img'    => $imgUrl
        );
        $res = M('user')->where(array('user_account' => $mobile))->find();
        if(empty($res)){
            $r = M('user')->where(array('user_wx_code' => $openid))->save($data);
        }elseif($res['user_wx_code'] == null){
            M('user')->where(array('user_wx_code' => $openid))->delete();
            $r = M('user')->where(array('user_account' => $mobile))->save($data);            
        }

        if($r){
            $useInfo = M('user')->where(array('user_wx_code' => $openid))->find();
            session('online_use_info', $useInfo);
            $this -> ajaxReturn(array('code' => 200, 'msg' => '注册成功！'));
        }
        $this -> ajaxReturn(array('code' => 404, 'msg' => '注册失败！'));
    }

    // 登录
    public function login(){
        $mobile = I('post.mobile');
        $password = md5(I('post.password'));
        $openid = session('openid');
        // 参数为空
        if(!$password || !$mobile){
            $this -> ajaxReturn(array('code' => 404, 'msg' => '账号密码不能为空'));
        }
        // 查看用户是否存在
        $useInfo = M('user')->where(array('user_account' => $mobile, 'user_pwd' => $password))->find();
        if(!$useInfo['user_wx_code']){
            // 获取微信头像
            $imgUrl = $this->getImg(); 
            if($useInfo['user_type'] == 2){
                $data = array(
                    'user_wx_code' => $openid,
                    'user_is_lx' => $useInfo['user_id'],
                    'user_head_img' => $imgUrl
                );
            }else{
                $data = array(
                    'user_wx_code' => $openid,
                    'user_is_lx' => cookie('pid'),
                    'user_head_img' => $imgUrl
                );
            }
            // 登录 查看是否有openid  有就先删除原先的  再写入保存
            M('user')->where(array('user_wx_code' => $openid))->delete();
            $r = M('user')->where(array('user_id' => $useInfo['user_id']))->save($data);
            if($r){
                session('online_use_info', $useInfo);
                if($useInfo['user_type'] == 2){ // 经销商登录
                    $company = $useInfo['user_company'];
                    $newPid = $useInfo['user_id'];
                    $img = $imgUrl;
                    $phone = $useInfo['user_account'];
                    cookie('company',$company);
                    cookie('pid',$newPid);
                    cookie('img',$img);
                    cookie('phone',$phone);
                }
                $this -> ajaxReturn(array('code' => 200, 'msg' => '登录成功'));
            }else{
                $this -> ajaxReturn(array('code' => 404, 'msg' => '登录失败，请联系小游'));
            }
        }
        session('online_use_info', $useInfo);
            if($useInfo['user_type'] == 2){ // 经销商登录
            $company = $useInfo['user_company'];
            $newPid = $useInfo['user_id'];
            $img = $useInfo['user_head_img'];
            $phone = $useInfo['user_account'];
            cookie('company',$company);
            cookie('pid',$newPid);
            cookie('img',$img);
            cookie('phone',$phone);
        }
        $this -> ajaxReturn(array('code' => 200, 'msg' => '登录成功'));
    }

    function getImg(){
        header("Content-Type:text/html;charset=utf-8");
        $openId = session('openid');
//        $tokenclass = new \Org\Custom\AccessToken();
//        $ACCESS_TOKEN=$tokenclass->gettoken();
        $wxService = D('WeiXinApi','Service');
        $ACCESS_TOKEN = $wxService->getAccessToken();
        $url="https://api.weixin.qq.com/cgi-bin/user/info?access_token=$ACCESS_TOKEN&openid=$openId&lang=zh_CN";
        $content = file_get_contents($url);
        $content = json_decode($content,true);
        return $content['headimgurl'];
    }
}