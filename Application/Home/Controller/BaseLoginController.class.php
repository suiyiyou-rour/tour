<?php
/**
 * Created by zhang.
 * User: zhang
 * Date: 2017/10/23
 * Time: 10:05
 */

namespace Home\Controller;


use Think\Controller;

class BaseLoginController extends Controller
{
    public $userId;
    public $userName;
    public $userType;

    public function __construct()
    {
        parent::__construct();
        header('Access-Control-Allow-Origin:*');
//        if(empty($_SESSION['id']) || empty($_SESSION['num']) || empty($_SESSION['type'])){
//            $this -> ajaxReturn(array('code' => '0','msg' => '你还没有登陆！'));
//        }
        $this->userId = $_SESSION['user_id'];
        $this->userName = $_SESSION['user_num'];
        $this->userType = 1;
        $this->userId = '111';
    }


}
