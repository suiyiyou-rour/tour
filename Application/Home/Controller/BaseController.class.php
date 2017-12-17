<?php

namespace Home\Controller;

use Think\Controller;

class BaseController extends Controller
{
    public $userId;
    public $userName;

    public function __construct()
    {
        parent::__construct();
        header('Access-Control-Allow-Origin:*');
        header("Access-Control-Allow-Methods", "GET,POST");
        header("Access-Control-Allow-Headers", "Origin, X-Requested-With, Content-Type, Accept");
//        if (empty($_SESSION['id']) || empty($_SESSION['num']) || empty($_SESSION['type'])) {
//
//        }
        $token = I('post.token');

        if (empty($token)) {
            $this->ajaxReturn(array('code' => '110', 'msg' => '你还没有登陆！'));
        }
        $res = $this->decrypt($token, $key = '123');
        $data = explode('-', $res);
        $where['sp_token'] = $token;
        $result = M('sp')->where($where)->find();
        if (!$result) {
            $this->ajaxReturn(array('code' => '1210', 'msg' => '你还没有登陆！'));
        }
        //if (time() - $data[0] > 3600 * 24 * 7) {
          //  $this->ajaxReturn(array('code' => '1310', 'msg' => '你还没有登陆！'));
        //}
        $this->userId = $data[1];
        $this->userName = $data[2];
        $_SESSION['type'] = $data[3];
//        $this->userId = '3434434';
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

    /**
     * 获取菜单
     *用户类型 1 供应商 2 分销商 3 超级管理员
     */
    public function getMenu()
    {
        $_SESSION['type'] = '1';
        if ($_SESSION['type'] == '1') {
            $this->ajaxReturn(C('gys'));
        } elseif ($_SESSION['type'] == '2') {
            $this->ajaxReturn(C('fxs'));
        } elseif ($_SESSION['type'] == 3) {
            $this->ajaxReturn(C('admin'));
        }
    }

    /**
     * 退出登录
     */
    public function loginOut(){
        M('sp')->where(array('sp_id' => $this -> userId))->save(array('sp_token' => ''));
    }
}
