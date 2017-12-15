<?php
/**
 * Created by zhang.
 * User: zhang
 * Date: 2017/10/23
 * Time: 10:21
 */

namespace Home\Controller;


use Think\Controller;

class UserLogin extends Controller
{

    /**
     * 游客登陆
     */
    public function userLogin()
    {
        $userName = I('post.name');
        $userPwd = md5(I('post.pwd'));
        $result = M('user')->where(array('user_account' => $userName, 'user_pwd' => $userPwd))->find();
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '登陆失败'));
        }
        $_SESSION['user_id'] = $result['user_id'];
        $_SESSION['user_mobile'] = $result['user_mobile'];
        $_SESSION['user_type'] = $result['user_type'];
        $this->ajaxReturn(array('code' => '0', 'msg' => '登陆成功', 'type' => $result['user_type'], 'userCode' => $result['user_id']));//1 游客 2 经销商
    }

    /**
     * 游客注册
     */
    public function userRegister()
    {
        $code = I('post.code');
        $mobile = I('post.mobile');
        $pwd = I('post.pwd');
        $result = M('sms_code')->where(array('c_code' => $code, 'c_mobile' => $mobile))->find();
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '验证码错误'));
        }
        $result = M('user')->add(array('user_account' => $mobile, 'user_mobile' => $mobile, 'user_pwd' => $pwd, 'user_regist_time' => time(), 'user_type' => 1));
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '注册失败'));
        }
        $this->ajaxReturn(array('code' => '0', 'msg' => '注册成功'));
    }

    /**
     * 经销商注册
     */
    public function jxsRegister()
    {
        $code = I('post.code');
        $mobile = I('post.mobile');
        $result = M('sms_code')->where(array('c_code' => $code, 'c_mobile' => $mobile))->find();
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '验证码错误'));
        }
        $account = I('post.email');
        $company = I('post.company');
        $province = I('post.province');
        $city = I('post.ciyt');
        $address = I('post.address');
        $lxmobile = I('post.lxmobile');//联系人手机号
        $lxname = I('post.lxname');//联系人名称
        $data['user_account'] = $account;
        $data['user_regist_time'] = time();
        $data['user_type'] = 2;
        $data['user_mobile'] = $mobile;
        $data['user_email'] = $account;
        $data['user_company'] = $company;
        $data['user_province'] = $province;
        $data['user_city'] = $city;
        $data['user_address'] = $address;
        $data['user_lx_mobile'] = $lxmobile;
        $data['user_lx_phone'] = $lxname;
        $data['user_is_lx'] = I('post.islx');
        $result = M('user')->add($data);
        if (!$result) {
            $this->ajaxReturn(array('code' => 0));
        } else {
            $this->ajaxReturn(array('code' => 1));
        }
    }

}