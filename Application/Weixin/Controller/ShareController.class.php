<?php
namespace Weixin\Controller;
use Think\Controller;
class ShareController extends Controller {
    public function index(){
        return;
    }

    // 海报获取
    public function getPoster()
    {
        $token = I('token');
        if($token != 'syy'){
            $this->ajaxReturn(array('code' => 404 ,'msg' => '指令错误'));
        }

        $posterData = M('img_table')->select();
        $this->ajaxReturn(array('code' => 200 ,'msg' => $posterData));
    }

    // 关闭门票库存
    public function login(){
        $isJxsCompany = cookie('company');
        $isJxsPhone = cookie('phone');
        if(!$isJxsCompany || !$isJxsPhone){
            $this->ajaxReturn(array('code' => 404 ,'msg' => '请注册成为经销商'));
        }
        $this->ajaxReturn(array('code' => 200 ,'msg' => "海报详情"));
    }

    // 关闭跟团库存
    public function getImageUrl(){
        $type = I('get.type');
        $code = I('get.code');

        // $isJxsCompany = cookie('company');
        // $isJxsPhone = cookie('phone');
        // if(!$isJxsCompany || !$isJxsPhone){
        //     $this->ajaxReturn(array('code' => 404 ,'msg' => '请注册成为经销商'));
        // }
        // if(!$type || !$code){
        //     $this->ajaxReturn(array('code' => 403 ,'msg' => '请注册成为经销商'));
        // }

        // 商品不在线上
       
        $tableWhere = $this->getType($type,$code);
        $res = M($type)->field('1')->where($tableWhere)->find();
        if(!$res){
            $this->ajaxReturn(array('code' => 403,'msg' => '商品暂不开放'));
        }
        // 生成二维码
        $where = array(
            'good_code' => $code,
            'good_type' => $type
        );
        $posterData = M('img_table')->where($where)->find();
        if(!$posterData){
            $this->ajaxReturn(array('code' => 403,'msg' => '暂无此海报信息'));
        }
        $imgUrl = $this->getPic($posterData['img_url'],$code,$type);
        $this->ajaxReturn(array('code' => 202,'msg' => $imgUrl));
    }

    public function qrcode($type,$code){
        $pid = cookie('pid');

        $save_path = isset($_GET['save_path'])?$_GET['save_path']:'./Public/qrcode/'; 
        $web_path = isset($_GET['save_path'])?$_GET['web_path']:'./Public/qrcode/';
        $qr_data = 'http://www.suiyiyou.net/index.php/Weixin/Detail/detail?shopType='.$type.'&shopCode='.$code.'&pid='.$pid;
        $qr_level = isset($_GET['qr_level'])?$_GET['qr_level']:'H';
        $qr_size = isset($_GET['qr_size'])?$_GET['qr_size']:'4'; // 二维码图片大小
        $save_prefix = isset($_GET['save_prefix'])?$_GET['save_prefix']:'ZETA';

        $filename = createQRcode($save_path,$qr_data,$qr_level,$qr_size,$save_prefix);
        $pic = $web_path.$filename;
        return $pic;
    }

    public function getPic($ImgPath,$code,$type){
        $bigImgPath = $ImgPath;
        $qCodePath = $this->qrcode($type,$code);

        $image = new \Think\Image();
        $image->open($bigImgPath);
        $image->open($bigImgPath)->water($qCodePath,\Think\Image::IMAGE_WATER_SOUTHEAST)->save("./Public/Page/image/".$code.$type.".jpg");
        return "./Public/Page/image/".$code.$type.".jpg";
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
                    'g_code'                      =>  array('eq',$code),
                    'g_is_del'                    =>  array('neq', '1'),                  // 未删除
                    'g_is_pass'                   =>  array('eq', '5'),                   // 5为上线
                    'unix_timestamp(g_on_time)'   =>  array('elt', $dt),                  // 上线时间小于等于今天
                    'unix_timestamp(g_d_time)'    =>  array('egt', $dt)                   // 下线时间大于等于今天
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