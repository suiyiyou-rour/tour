<?php
namespace Page\Controller;
use Think\Controller;
class LoginController extends Controller {
    //游客登陆页面
    public function login(){
        $this->display("login/login");
    }

    //游客注册页面
    public function register(){
        $this->display("login/register");
    }

    //游客注册成功后 选择成为分销商页面
    public function fregister(){
        $this->display("login/fregister");
    }
}