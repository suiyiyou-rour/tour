<?php
/**
 * 订单 以及详情
 */

namespace Page\Controller;
use Think\Controller;
class OrderController extends Controller
{
    // public function __construct(){
    //     parent::__construct();
    //     header("Access-Control-Allow-Origin: *");
    // }
    
    // code 查询
    public function getCode()
    {
        $type = I('post.type');
        $code = I('post.code');

        if($type== '' || $code == ''){
            $this->ajaxReturn(array('code' => '404', 'msg' => '非法请求'));
        }

        if($type == 'group'){
            $res = $this -> groupCode($code);
        }elseif($type == 'tick'){
            $res = $this -> tickCode($code);
        }elseif($type == 'scenery'){
            $res = $this -> sceneryCode($code);
        }
        if(!$res){
            $res = [];
        }
        $data = array(
            'code' => 200,
            'data' => $res
        );
        $this->ajaxReturn($data);
    }
    // 跟团游 code 查询
    private function groupCode($code){
        $user_code ='18060481803';
      
        $where = array(
            'g_order_sn' => $code,
            'g_mobile' => $user_code
        );
        $data = M('group_order')->field('g_group_name name,g_group_code scode,g_order_sn code,g_order_price price,g_order_type status,g_go_time gtime')->where($where)->find();

        
        if(!empty($data)){

            $orderTime = $data['code'];
            $data['time'] = substr($orderTime,0,4).'-'.substr($orderTime,4,2).'-'.substr($orderTime,6,2);
            $data['type'] = 'group';

            $imgArr = M('group')->field('g_file')->where('g_code='.$data['scode'])->find();
            
            $imgArr = json_decode($imgArr['g_file'],true);
            foreach ($imgArr as $i) {
                if ($i['head'] === 'true') {
                    $data['img'] = C('img_url') . $i['src'];
                    break;
                }
            }
            if (empty($data['img'])) {
                $data['img'] = C('img_url') . $imgArr[0]['src'];
            }
        }
        return $data = [$data];
    }
    // 门票 code 查询
    private function tickCode($code){

        $user_code ='18060481803';

        $where = array(
            't_order_sn' => $code,
            't_order_user_mobile' => $user_code
        );
        $data = M('tick_order')->field('t_tick_name name,t_tick_code scode,t_order_sn code,t_tick_price price,t_tick_order_type status,t_go_time gtime')->where($where)->find();
        
        
        if(!empty($data)){
            $orderTime = $data['code'];
            $data['time'] = substr($orderTime,0,4).'-'.substr($orderTime,4,2).'-'.substr($orderTime,6,2);
            $data['type'] = 'tick';
            foreach($data as $k => $v){
                $imgArr = M('tick')->field('t_tick_file')->where('t_code='.$v['scode'])->find();
                
                $imgArr = json_decode($imgArr['t_tick_file'],true);
                foreach ($imgArr as $i) {
                    if ($i['head'] === 'true') {
                        $data[$k]['img'] = C('img_url') . $i['src'];
                        break;
                    }
                }
                if (empty($data[$k]['img'])) {
                    $data[$k]['img'] = C('img_url') . $imgArr[0]['src'];
                }
            }
        }
        return $data = [$data];
    }
    // 景酒 code 查询
    private function sceneryCode($code){
        $user_code ='18060481803';

        $where = array(
            'o_order_sn' => $code,
            'o_mobile' => $user_code
        );

        $data = M('seceny_order')->field('o_name name,o_seceny_code scode,o_order_sn code,o_order_price price,o_order_type status,o_user_time gtime')->where($where)->find();
        
        if(!empty($data)){
             
            $orderTime = $data['code'];
            $data['time'] = substr($orderTime,0,4).'-'.substr($orderTime,4,2).'-'.substr($orderTime,6,2);
            $data['type'] = 'scenery';

            foreach($data as $k => $v){
                $imgArr = M('scenery')->field('s_img')->where('s_code='.$v['scode'])->find();
                
                $imgArr = json_decode($imgArr['s_img'],true);
                foreach ($imgArr as $i) {
                    if ($i['headImg'] === 'true') {
                        $data[$k]['img'] = C('img_url') . $i['imgtitle'];
                        break;
                    }
                }
                if (empty($data[$k]['img'])) {
                    $data[$k]['img'] = C('img_url') . $imgArr[0]['imgtitle'];
                }
            }
        }
        return $data = [$data];
    }

    // keyWord 查询
    public function getKey()
    {
        $type = I('post.type');
        $code = I('post.key');
        $status = I('post.status');

        if($type== '' || $key == '' || $status == ''){
            $this->ajaxReturn(array('code' => '404', 'msg' => '非法请求'));
        }

        if($type == 'group'){
            $res = $this -> groupKey($code,$status);
        }elseif($type == 'tick'){
            $res = $this -> tickKey($code,$status);
        }elseif($type == 'scenery'){
            $res = $this -> sceneryKey($code,$status);
        }
        if(!$res){
            $res = [];
        }
        $data = array(
            'code' => 200,
            'data' => $res
        );
        $this->ajaxReturn($data);
    }

    // 跟团游 key 查询
    private function  groupKey($code,$status){
        $user_code ='18060481803';
        
        $where = array(
            'g_group_name' => array('like','%'.$code.'%'),
            'g_mobile' => $user_code,
            'g_order_type' => $status
        );
        $data = M('group_order')->field('g_group_name name,g_group_code scode,g_order_sn code,g_order_price price,g_order_type status,g_go_time gtime')->where($where)->order('g_order_sn desc')->select();

        if(!empty($data)){
            foreach($data as $k => $v){
                // 下单日期，类型
                $orderTime = $data[$k]['code'];
                $data[$k]['time'] = substr($orderTime,0,4).'-'.substr($orderTime,4,2).'-'.substr($orderTime,6,2);
                $data[$k]['type'] = 'group';
           
                // 首图
                $imgArr = M('group')->field('g_file')->where('g_code='.$data[$k]['scode'])->find();
                $imgArr = json_decode($imgArr['g_file'],true);
                foreach ($imgArr as $i) {
                    if ($i['head'] === 'true') {
                        $data[$k]['img'] = C('img_url') . $i['src'];
                        break;
                    }
                }
                if (empty($data[$k]['img'])) {
                    $data[$k]['img'] = C('img_url') . $imgArr[0]['src'];
                }
            }
        }
        return $data;
    }

    // 门票 key  查询
    private function tickKey($code,$status){
        $user_code ='18060481803';
        
        $where = array(
            't_tick_name' => array('like','%'.$code.'%'),
            't_order_user_mobile' => $user_code,
            't_tick_order_type' => $status
        );
        $data = M('tick_order')->field('t_tick_name name,t_tick_code scode,t_order_sn code,t_tick_price price,t_tick_order_type status,t_go_time gtime')->where($where)->order('t_order_sn desc')->select();
        
        
        if(!empty($data)){
            foreach($data as $k => $v){
                // 下单日期，类型
                $orderTime = $data[$k]['code'];
                $data[$k]['time'] = substr($orderTime,0,4).'-'.substr($orderTime,4,2).'-'.substr($orderTime,6,2);
                $data[$k]['type'] = 'tick';

                $imgArr = M('tick')->field('t_tick_file')->where('t_code='.$v['scode'])->find();
                
                $imgArr = json_decode($imgArr['t_tick_file'],true);
                foreach ($imgArr as $i) {
                    if ($i['head'] === 'true') {
                        $data[$k]['img'] = C('img_url') . $i['src'];
                        break;
                    }
                }
                if (empty($data[$k]['img'])) {
                    $data[$k]['img'] = C('img_url') . $imgArr[0]['src'];
                }
            }
        }
        return $data;
    }

    // 酒景 key 查询 
    private function sceneryKey($code,$status){
        $user_code ='18060481803';
        
        $where = array(
            'o_name' => $code,
            'o_mobile' => $user_code,
            'o_order_type' => $status
        );

        $data = M('seceny_order')->field('o_name name,o_seceny_code scode,o_order_sn code,o_order_price price,o_order_type status,o_user_time gtime')->where($where)->order('o_order_sn desc')->select();
        
        if(!empty($data)){
            foreach($data as $k => $v){
                $orderTime = $data[$k]['code'];
                $data[$k]['time'] = substr($orderTime,0,4).'-'.substr($orderTime,4,2).'-'.substr($orderTime,6,2);
                $data[$k]['type'] = 'scenery';
    
                
                $imgArr = M('scenery')->field('s_img')->where('s_code='.$v['scode'])->find();
                
                $imgArr = json_decode($imgArr['s_img'],true);
                foreach ($imgArr as $i) {
                    if ($i['headImg'] === 'true') {
                        $data[$k]['img'] = C('img_url') . $i['imgtitle'];
                        break;
                    }
                }
                if (empty($data[$k]['img'])) {
                    $data[$k]['img'] = C('img_url') . $imgArr[0]['imgtitle'];
                }
            }
        }
        return $data;
    }

    // stastus 查询
    public function  getStatus(){
        $type = I('post.type');
        $status = I('post.status');

        if($type== ''|| $status == ''){
            $this->ajaxReturn(array('code' => '404', 'msg' => '非法请求'));
        }

        if($type == 'group'){
            $res = $this -> groupStatus($status);
        }elseif($type == 'tick'){
            $res = $this -> tickStatus($status);
        }elseif($type == 'scenery'){
            $res = $this -> sceneryStatus($status);
        }
        if(!$res){
            $res = [];
        }
        $data = array(
            'code' => 200,
            'data' => $res
        );
        $this->ajaxReturn($data);
    }

    // 跟团游 status 查询
    public function groupStatus($status){
        $user_code ='18060481803';
        
        $where = array(
            'g_mobile' => $user_code,
            'g_order_type' => $status
        );
        $data = M('group_order')->field('g_group_name name,g_group_code scode,g_order_sn code,g_order_price price,g_order_type status,g_go_time gtime')->where($where)->order('g_order_sn desc')->select();
        
        if(!empty($data)){
            foreach($data as $k => $v){
                // 下单日期，类型
                $orderTime = $data[$k]['code'];
                $data[$k]['time'] = substr($orderTime,0,4).'-'.substr($orderTime,4,2).'-'.substr($orderTime,6,2);
                $data[$k]['type'] = 'group';
            
                // 首图
                $imgArr = M('group')->field('g_file')->where('g_code='.$data[$k]['scode'])->find();
                $imgArr = json_decode($imgArr['g_file'],true);
                foreach ($imgArr as $i) {
                    if ($i['head'] === 'true') {
                        $data[$k]['img'] = C('img_url') . $i['src'];
                        break;
                    }
                }
                if (empty($data[$k]['img'])) {
                    $data[$k]['img'] = C('img_url') . $imgArr[0]['src'];
                }
            }
        }
        return $data;
    }
    // 门票 status 查询
    public function tickStatus($status){
        $user_code ='18060481803';
        
        $where = array(
            't_order_user_mobile' => $user_code,
            't_tick_order_type' => $status
        );

        $data = M('tick_order')->field('t_tick_name name,t_tick_code scode,t_order_sn code,t_tick_price price,t_tick_order_type status,t_go_time gtime')->where($where)->order('t_order_sn desc')->select();
        
        if(!empty($data)){
            foreach($data as $k => $v){
                // 下单日期，类型
                $orderTime = $data[$k]['code'];
                $data[$k]['time'] = substr($orderTime,0,4).'-'.substr($orderTime,4,2).'-'.substr($orderTime,6,2);
                $data[$k]['type'] = 'tick';

                $imgArr = M('tick')->field('t_tick_file')->where('t_code='.$v['scode'])->find();
                
                $imgArr = json_decode($imgArr['t_tick_file'],true);
                foreach ($imgArr as $i) {
                    if ($i['head'] === 'true') {
                        $data[$k]['img'] = C('img_url') . $i['src'];
                        break;
                    }
                }
                if (empty($data[$k]['img'])) {
                    $data[$k]['img'] = C('img_url') . $imgArr[0]['src'];
                }
            }
        }
        return $data;
    }
    // 酒景 status 查询
    public function sceneryStatus($status){
        $user_code ='18060481803';
        
        $where = array(
            'o_mobile' => $user_code,
            'o_order_type' => $status
        );

        $data = M('seceny_order')->field('o_name name,o_seceny_code scode,o_order_sn code,o_order_price price,o_order_type status,o_user_time gtime')->where($where)->order('o_order_sn desc')->select();
        
        if(!empty($data)){
            foreach($data as $k => $v){
                $orderTime = $data[$k]['code'];
                $data[$k]['time'] = substr($orderTime,0,4).'-'.substr($orderTime,4,2).'-'.substr($orderTime,6,2);
                $data[$k]['type'] = 'scenery';
    
                
                $imgArr = M('scenery')->field('s_img')->where('s_code='.$v['scode'])->find();
                
                $imgArr = json_decode($imgArr['s_img'],true);
                foreach ($imgArr as $i) {
                    if ($i['headImg'] === 'true') {
                        $data[$k]['img'] = C('img_url') . $i['imgtitle'];
                        break;
                    }
                }
                if (empty($data[$k]['img'])) {
                    $data[$k]['img'] = C('img_url') . $imgArr[0]['imgtitle'];
                }
            }
        }
        return $data;
    }
}