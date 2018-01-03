<?php
/**
 *  页面以及 列表接口
 */
namespace Page\Controller;
use Think\Controller;
class HomeController extends Controller {
    /*主页面显示*/
    public function index(){
        $this->display('home/index');
    }


}
