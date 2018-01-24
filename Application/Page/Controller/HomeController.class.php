<?php
/**
 *  页面以及 列表接口
 */
namespace Page\Controller;
use Think\Controller;
class HomeController extends Controller {
    public function __construct(){
        parent::__construct();
        $pass = array('login','index','show');
        if(!in_array(ACTION_NAME,$pass)){
            if(empty(session('UserAdminLogin'))){
                $this->display('home/index');
            }
            die;
        }

    }

    /*主页面显示*/
    public function index(){
        $this->display('home/index');
    }

    public function login(){
        $user = I("post.user");
        $pwd = I("post.pwd");
        if(empty($user) || empty($pwd)){
            $this->error('账号密码不能为空','index',1);
        }
        if($user != "syy" || $pwd !="syy123" ){
            $this->error('账号密码不能为空','index',1);
        }
        $res = session('UserAdminLogin',1);
        $this->display('home/back');
    }

}
