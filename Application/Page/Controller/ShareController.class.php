<?php
namespace Page\Controller;

use Think\Controller;

class ShareController extends Controller
{
    public function __construct()
    {
        parent::__construct();

        $is_login = session('shareIsLogin');
        if($is_login){
            $this->redirect('index');
        }
    }
    
    // 登录页面
    public function index(){
        $this->display('share/login');
    }

    // 登录验证
    public function login(){

        $id = I('post.id');
        $password = I('post.pwd');

        if($id == 'syy' && $password == 'syy'){
            session('shareIsLogin',1);
            $this->ajaxReturn(array('code' => 200));
        }
    }

    // 后台页面
    public function back(){
        $this->display('share/back');
    }

    // 海报处理
    public function imgGet(){
        $type = I('post.type');
        $code = I('post.code');
        
        // 参数完整性
        if(!$type || !$code){
            $this->ajaxReturn(array('code' => 403,'msg' => '参数错误'));
        }

        $tableWhere = $this->getType($type,$code);
        $res = M($type)->where($tableWhere)->find();

        // 商品不存在
        if(!$res){
           $this->ajaxReturn(array('code' => 403,'msg' => '商品码错误'));
        }
        
        // 海报图片上传
        $res = $this->imgDeal($_FILES['file'],$code); 
        if($res['code'] == 0){
            $this->ajaxReturn(array('code' => 403,'msg' =>$res['message'] ));
        }
        $where = array(
            'good_code' => $code,
            'good_type' => $type
        );
        $id = M('img_table')->field('1')->where($where)->find();

        $saveData = array(
            'good_code' => $code,
            'good_type' => $type,
            'img_url'   => $res['message']
        );
        if($id){
            $sqlRes = M('img_table')->where($where)->save($saveData);
        }else{
            $sqlRes = M('img_table')->add($saveData);
        }
        $this->ajaxReturn(array('code' => 200,'msg' =>'上传成功'));
        // 新增海报
    }

    /** 对上传的图片进行数据的判断处理
     *  @param $_FILES - PHP文档数据的存储
     *  @return  $result - 判断处理结果 
     */
    private function imgDeal($image,$fileName){

        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728;// 设置附件上传大小
        $upload->exts = array('jpg', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = './Public/Page/poster/'; // 设置附件上传根目录
        $upload->replace = true; // 覆盖同名文件
        $upload->autoSub  = false; // 子目录储存
        $upload->saveName =  $fileName;

        $info = $upload->uploadOne($image);
        if (!$info) {// 上传错误提示错误信息
            return array("code"=>0,"message"=>$upload->getError());
        } else {// 上传成功 获取上传文件信息
            $image_k="";
            //取出数组里的名字和文件夹
            $image_k .= "./Public/Page/poster/".$info["savepath"] . $info["savename"].'?t='.time();
            return array("code"=>1,"message"=>$image_k);
        }
    }



    /** 商品类型获取 暂不考虑其他值的情况
    *   @param $type - 字符类型
    *   @return $tableType - 查表类型
    */
    private function getType($type,$code){
        $dd = date("Y-m-d", time());
        $dt = strtotime($dd);

        switch($type){
            case "tick":
                $where = array(
                    't_code'                            =>  $code,            // 商品id
                    't_tick_type'                       =>  '4',               // 上线产品
                    't_tick_del'                        =>  array('neq', '1'), // 未被删除
                    'unix_timestamp(t_tick_sj_time)'    =>  array('elt', $dt), // 上线时间小于等于今天
                    'unix_timestamp(t_tick_xj_time)'    =>  array('egt', $dt)  // 下线时间大于等于今天
                );
            break;
            case "group":
                $where = array(
                    'a.g_code'                      =>  array('eq',$code),
                    'a.g_is_del'                    =>  array('neq', '1'),                  // 未删除
                    'a.g_is_pass'                   =>  array('eq', '5'),                   // 5为上线
                    'unix_timestamp(a.g_on_time)'   =>  array('elt', $dt),                  // 上线时间小于等于今天
                    'unix_timestamp(a.g_d_time)'    =>  array('egt', $dt)                   // 下线时间大于等于今天
                );
            break;
            case "scenery":
                $where = array(
                    's_code'                    =>  $code,
                    's_type'                    =>  '5',
                    's_is_del'                  =>  array('neq', '1'),    // 未删除
                    'unix_timestamp(s_sj_time)' =>  array('elt', $dt),    // 上线时间小于等于今天
                    'unix_timestamp(s_xj_time)' =>  array('egt', $dt)     // 下线时间大于等于今天
                );
        }
        return $where;
    }
}