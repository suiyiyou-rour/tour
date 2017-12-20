<?php
namespace Weixin\Controller;
use Think\Controller;
class CrontabController extends Controller {
    
    // 预定须知 时间超过一天的全部关闭库存 
    public function action()
    {
        $token = I('get.token');
        if($token == 'suiyiyou123'){

            $result1 = $this->tickChange();
        //$result2 = $this->groupChange();
        }
        $this->ajaxReturn(array('code'=>404 ,'msg' => 'token错误！'));
    }

    // 关闭门票库存
    private function tickChange(){

        $dd = date("Y-m-d", time());
        $dt = strtotime($dd);

        $where = array(
            't_tick_del' => array('neq', '1'),
            't_tick_type' => array('eq', '4'),
            'unix_timestamp(t_tick_sj_time)' => array('elt', $dt),
            'unix_timestamp(t_tick_xj_time)' => array('egt', $dt)
        );
        $data = M('tick')->field('t_id id,t_tick_pre_book_time time')->where($where)->select();
        foreach($data as $k=>$v){
            $data[$k]['time'] = json_decode($v['time']);
        }
        var_dump($data);
    }

    // 关闭跟团库存
    private function groupChange(){
        
    }
}