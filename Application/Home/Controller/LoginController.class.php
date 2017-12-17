<?php
/**
 * Created by zhang.
 * User: zhang
 * Date: 2017/10/19
 * Time: 9:29
 */

namespace Home\Controller;


use Think\Controller;

class LoginController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        header('Access-Control-Allow-Origin:*');
        header("Access-Control-Allow-Methods", "GET,POST");
        header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
    }

    /**
     * 供应商登陆
     */
    public function supplierLogin()
    {
        $account = I('post.num');
        $pwd = md5(trim(I('post.pwd')));
        if (empty($account) || empty($pwd)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '账号或者密码不能为空'));
        }
	        $where['sp_account_num'] = $account;
        $where['sp_pwd'] =$pwd;
        $where['sp_open'] = 1;
	
        $result = M('sp')->where($where)->find();

        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '账号或者密码错误或者账号被冻结'));
        } else {
		if(empty($result['sp_token'])){
                $data = time() . "-" . $result['sp_id'] . "-" . $result['sp_account_num'] . "-" . $result['sp_type'];
                $token = $this->encrypt($data, '123');
            }else{
                $token = $result['sp_token'];
            }

          M('sp')->where($where)->save(array('sp_is_login' => 1, 'sp_login_time' => time(), 'sp_token' => $token));

            $this->ajaxReturn(array('code' => '1', 'msg' => '登陆成功', 'type' => $result['sp_type'], 'name' => $result['sp_account_num'], 'token' => $token));
        }
    }

    public function encrypt($data, $key)
    {
        $key = md5($key);
        $x = 0;
        $len = strlen($data);
        $l = strlen($key);
        $char = '';
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= $key{$x};
            $x++;
        }
        for ($i = 0; $i < $len; $i++) {
            $str .= chr(ord($data{$i}) + (ord($char{$i})) % 256);
        }
        return base64_encode($str);
    }

    /**
     * 经销商注册
     */
    public function jsxRegister()
    {
        $num = I('post.num');
        $code = I('post.code');
        $result = M('sms_code')->where(array('c_mobile' => $num, 'c_code' => $code))->find();
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '验证码错误'));
        }
        M('sms_code')->where(array('c_mobile' => $num, 'c_code' => $code))->delete();
        $user['sp_account_num'] = $num;
        $user['sp_pwd'] = md5(I('post.pwd'));
        $user['sp_type'] = 2;
        $user['sp_com_name'] = I('post.company');
        $user['sp_mobile'] = I('post.mobile');
        $user['sp_name'] = I('post.name');
        $user['sp_qq'] = I('post.qq');
        $user['sp_email'] = I('post.email');
        $user['sp_address'] = I('post.address');
        $img = I('post.img');
        $path = "./Public/jsx/";
        $user['sp_file'] = $this->addr($img, $img, 1, $path);
        $result = M('sp')->add($user);
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '注册失败'));
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '注册成功'));
    }


    /**
     * 修改密码
     */
    public function changePwd()
    {
        $mobile = I('post.mobile');
        $oldPwd = md5(I('post.op'));//旧密码
        $newPwd = md5(I('post.np'));//新密码
        $result = M('sp')->where(array('sp_account_num' => $mobile, 'sp_pwd' => $oldPwd))->find();
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '密码错误不能修改密码'));
        }
        $result = M('sp')->where(array('sp_account_num' => $mobile))->save(array('sp_pwd' => $newPwd));
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '密码修改失败'));
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '密码修改成功'));
    }

    /**
     * 上传图片
     */
    public function addr($file, $data, $i, $path)
    {
        $data = str_replace(" ", "", $data);
        if (preg_match('/^(data:\s*image\/(\w+);base64)/', $file, $result)) {
            $type = $result[2];
            $new_file = $path . time() . $i . ".{$type}";
            file_put_contents($new_file, base64_decode($data));
            return $new_file;
        }
    }

    public function checkLogin()
    {
        $token = I('post.token');
        if (empty($token)) {
            $this->ajaxReturn(array('code' => '110', 'msg' => '你还没有登陆！'));
        }
        $res = $this->decrypt($token, $key = '123');
        $data = explode('-', $res);
        $where['sp_token'] =$token;
        $result = M('sp')->where($where)->find();
//echo M('sp')->_sql();
        if (!$result) {
         $this->ajaxReturn(array('code' => '110', 'msg' => '你还没有登陆！'));
        }
        //if (time() - $data[0] > 3600 * 24 * 7) {
          //  $this->ajaxReturn(array('code' => '110', 'msg' => '你还没有登陆！'));
        //}
        $this->ajaxReturn(array('code' => '1', 'msg' => '已经登陆！'));
    }

    public function decrypt($data, $key)
    {
        $key = md5($key);
        $x = 0;
        $data = base64_decode($data);
        $len = strlen($data);
        $l = strlen($key);
        $char = '';
        $str = '';
        for ($i = 0; $i < $len; $i++) {
            if ($x == $l) {
                $x = 0;
            }
            $char .= substr($key, $x, 1);
            $x++;
        }
        for ($i = 0; $i < $len; $i++) {
            if (ord(substr($data, $i, 1)) < ord(substr($char, $i, 1))) {
                $str .= chr((ord(substr($data, $i, 1)) + 256) - ord(substr($char, $i, 1)));
            } else {
                $str .= chr(ord(substr($data, $i, 1)) - ord(substr($char, $i, 1)));
            }
        }
        return $str;
    }

}
