<?php
/**
 * 订单 以及详情
 */
namespace Weixin\Controller;
class OrderCenterController extends BaseController
{

    /**
     *  获取订单详情
     *  @param type 订单类型
     *  @param status 订单状态
     */
    public function getType(){

        $type = I('shopType');
        $status = I('status');
        if($type== '' || $status == ''){
            $this->ajaxReturn(array('code' => '404', 'msg' => '非法请求'));
        }
         $userCode = '18159081124';
      //  $userCode = $_SESSION["online_use_info"]["user_account"]; // 用户code
        if(!$userCode){
            $this->ajaxReturn(array('code' => '404', 'msg' => '用户没有登陆'));
        }

        $usertype = $_SESSION["online_use_info"]["user_type"];
        $userPid = M('user')->field('user_id,user_account')->where('user_account='.$userCode)->find();
        $userPid = $userPid['user_id'];
        $page = I('page');
        $page = $page ? 0 : $page - 1;

        if($type == 'group'){
            $res = $this -> getGroup($status,$userCode,$userPid,$page,$usertype);
        }elseif($type == 'scenery'){
            $res = $this -> getScenery($status,$userCode,$userPid,$page,$usertype);
        }if($type == 'tick'){
            $res = $this -> getTick($status,$userCode,$userPid,$page,$usertype);
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

    /**
     *  获取跟团游订单
     */
    private function getGroup($status,$userCode,$userPid,$page,$usertype){
//        $userCode = '18060481803'; // 模拟数据
        // 判断是不是经销商的订单
        $where = 'g_order_type='.$status.' and g_add_order_user='.$userCode;
        if($usertype == 2){
            $where = 'g_order_type='.$status.' and (g_add_order_user='.$userCode.' or g_jxs_code ='.$userPid.')';
        }
        $data = M('group_order')->field('g_group_name name,g_add_order_user userid,g_group_code scode,g_order_sn code,g_order_price price,g_order_type status')->where($where)->order('g_order_sn desc')->limit($page*10,10)->select();
        if(!empty($data)){
            foreach($data as $k => $v){
                $imgArr = M('group')->field('g_file')->where('g_code='.$v['scode'])->find();
                
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
                if($v['userid']==$userCode){
                    $data[$k]['userid'] = 1;
                }
            }
        }
        return $data;
    }

    private function getTick($status,$userCode,$userPid,$page){
//        $userCode = '18060481803'; // 模拟数据
        $where = 't_tick_order_type='.$status.' and  t_order_user_id='.$userCode;
        if($usertype == 2){
            $where = 't_tick_order_type='.$status.' and  (t_order_user_id='.$userCode.' or t_jsx_code ='.$userPid.')';
        }
        $data = M('tick_order')->field('t_tick_name name,t_order_user_id userid,t_tick_code scode,t_order_sn code,t_tick_price price,t_tick_order_type status')->where($where)->order('t_order_sn desc')->limit($page*10,10)->select();
        
        if(!empty($data)){
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
                if($v['userid']==$userCode){
                    $data[$k]['userid'] = 1;
                }
            }
        }
        return $data;
    }
    

    private function getScenery($status,$userCode,$userPid,$page){
//        $userCode = '18060481803'; // 模拟数据
        $where = 'o_order_type='.$status.'  and  o_order_add_user='.$userCode;
        if($usertype == 2){
            $where = 'o_order_type='.$status.'  and  (o_order_add_user='.$userCode.' or o_jxs_code ='.$userPid.')';
        }
        $data = M('tick_order')->field('o_name name,o_order_add_user userid,o_seceny_code scode,o_order_sn code,o_order_price price,o_order_type status')->where($where)->order('o_order_sn desc')->limit($page*10,10)->select();
        
        if(!empty($data)){
            foreach($data as $k => $v){
                $imgArr = M('scenery')->field('s_img')->where('s_code='.$v['scode'])->find();
                
                $imgArr = json_decode($imgArr['o_file'],true);
                foreach ($imgArr as $i) {
                    if ($i['headImg'] === 'true') {
                        $data[$k]['img'] = C('img_url') . $i['imgtitle'];
                        break;
                    }
                }
                if (empty($data[$k]['img'])) {
                    $data[$k]['img'] = C('img_url') . $imgArr[0]['imgtitle'];
                }
                if($v['userid']==$userCode){
                    $data[$k]['userid'] = 1;
                }
            }
        }
        return $data;
    }
}