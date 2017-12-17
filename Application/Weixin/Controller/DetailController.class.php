<?php
/**
 * 详情接口
 */
namespace Weixin\Controller;
class DetailController extends BaseController {
    public function index(){}

    // 商品详情
    public function detail()
    {
        header("Content-Type:text/html;charset=utf-8");

        $type = I('shopType');  // 商品类型
        $code = I('shopCode');  // 商品code
        
        if (empty($code) || empty($type)) {
            $this->ajaxReturn(array('code' => '404', 'data' => '','msg' => '请提交产品code和商品类型！'));
        }
        
        // 商品类型
        if($type == 'group'){
            $res = $this->gDetail($code);
        }elseif($type == 'tick'){
            $res = $this->tDetail($code);
        }elseif($type == 'scenery'){
            $res = $this->sDetail($code);
        }
        // var_dump($res);
        $this->ajaxReturn($res);
    }

    // 门票详情
    private function tDetail($scode)
    {
        $dd  =  date("Y-m-d", time());  // 当前时间
        $dt  =  strtotime($dd);         // 转化成秒数

        $where = array(
            't_code'                            =>  $scode,            // 商品id
            't_tick_type'                       =>  '4',               // 上线产品
            't_tick_del'                        =>  array('neq', '1'), // 未被删除
            'unix_timestamp(t_tick_sj_time)'    =>  array('elt', $dt), // 上线时间小于等于今天
           'unix_timestamp(t_tick_xj_time)'    =>  array('egt', $dt)  // 下线时间大于等于今天
        );
        
        $data =  M('tick')->where($where)->find();
        
        //判断是否无此数据
        if(empty($data)){
            return $res = array(
                'code'  =>  '403',                  //状态值
                'data'  =>  [],                     //数据
                'msg'   =>  '无此数据，请重新搜索！'  //消息
            );
        };

        // json数据重组
        $data['t_tick_cost']            =   json_decode($data['t_tick_cost'],true);
        $data['t_tick_file']            =   json_decode($data['t_tick_file'],true);
        $data['t_tick_use_address']     =   json_decode($data['t_tick_use_address'],true);
        $data['t_tickService_num']      =   json_decode($data['t_tickService_num'],true);
        $data['t_go_b_time']            =   json_decode($data['t_go_b_time'],true);
        $data['t_tick_pre_book_time']   =   json_decode($data['t_tick_pre_book_time'],true);
        $data['t_tick_identity']        =   json_decode($data['t_tick_identity'],true);
        $data['t_tick_playerInfo']      =   json_decode($data['t_tick_playerInfo'],true);
        $data['t_yd_num']               =   json_decode($data['t_yd_num'],true);

        // 图片地址
        foreach ($data['t_tick_file'] as $k => $val) {
            $data['t_tick_file'][$k]['src'] = C('img_url') . $val['src'];
        }

       
        // 判断 价格日历 1、有效期
        if($data['t_tick_date'] == 1){
            $validity = M('tick_y')
                        ->where(array('y_code' => $scode))
                        ->select();
            
            $date = $this->getDateFromRange($validity[0]['y_b_time'],$validity[0]['y_e_time']);
            
            $use_time =json_decode($validity[0]['y_can_use_time'],true);
            $no_use_time =json_decode($validity[0]['y_no_user_time'],true);
            $data['use_time'] = $use_time;
            $data['no_use_time'] = $no_use_time;
            foreach($date as $k=>$v){
                $dateArr['p_date'] = $v;
                $dateArr['p_mark_price'] = $data['t_tick_mark_price'];
                $dateArr['p_js_price'] = $data['t_tick_settle_price'];
                $dateArr['p_my_price'] = $data['t_tick_my_price'];
                $dateArr['p_code'] = $scode;
                $dateArr['p_user_code'] = $validity[0]['y_user_code'];
                $dateArr['p_ck'] = $data['t_tick_kc'];
                $date[$k] = $dateArr;
            }
        }else{

            $date =  M('tick_price')
            -> where(array(
                'p_is_open'              =>  '1',                   // 处于公开状态        
                'p_code'                 =>  $scode,                // 商品code
                'unix_timestamp(p_date)' =>  array('egt', $dt))     // 开始时间
            )
            ->select();
            $date   = $this->array_sort($date,'p_date');   // 按时间先后排序
        }

        $newArr = $this->newDate($date,'p_date');      // 重新构造日历数组
        
        $data['date'] = $newArr;
        if(!$data){
            $data = [];
        }
        return $res = [
            'code' =>  '200',
            'data' =>  $data,
            'msg'  =>  'tick'
        ];
    }

    // 酒店详情
    private function sDetail($tcode)
    {
        $dd = date("Y-m-d", time());
        $dt = strtotime($dd);

        $where = array(
            's_code'                    =>  $tcode,
            's_type'                    =>  '5',
            's_is_del'                  =>  array('neq', '1'),    // 未删除
            'unix_timestamp(s_sj_time)' =>  array('elt', $dt),    // 上线时间小于等于今天
            'unix_timestamp(s_xj_time)' =>  array('egt', $dt)     // 下线时间大于等于今天
        );
        $data = M('scenery')->where($where)->find();

        // 判断是否无此数据
        if(empty($data)){
            return $res = array(
                'code'   =>   '403',
                'data'   =>   [],
                'msg'    =>   '无此数据，请重新搜索！'
            );
        };

        // json 数据解析
        $data['s_view']              =  json_decode($data['s_view'],true);           // 景点
        $data['s_hotel_t_info']      =  json_decode($data['s_hotel_t_info'],true);   // 酒店信息
        $data['s_tj_ly']             =  json_decode($data['s_tj_ly'],true);          // 推介理由
        $data['s_img']               =  json_decode($data['s_img'],true);            // 图片
        $data['s_tag']               =  json_decode($data['s_tag'],true);            // 标签
        $data['s_food']              =  json_decode($data['s_food'],true);           // 餐饮编码

        // 图片路径重组
        foreach ($data['s_img'] as $k => $val) {
            $data['s_img'][$k]['imgtitle'] = C('img_url') . $val['imgtitle'];
        }

        if($data['s_tick_date'] == 1){
            $date = M('scenery_yx')
                    ->where(array(
                        'y_is_open'                 =>  '1',
                        'y_code'                    =>  $tcode,
                        'unix_timestamp(y_b_time)'  =>  array('egt', $dt))
                        )
                    ->select();

            $date   = $this->array_sort($date,'y_b_time'); // 按时间排序
            $newArr = $this->newDate($date,'y_b_time');    // 对价格日历
        }else{
            $date = M('scenery_price')
                    ->where(array(
                        'p_is_open'                 =>  '1',
                        'p_code'                    =>  $tcode,
                        'unix_timestamp(p_b_time)'  =>  array('egt', $dt))
                        )
                    ->select();

            $date   = $this->array_sort($date,'p_b_time'); // 按时间排序
            $newArr = $this->newDate($date,'p_b_time');    // 对价格日历
        }



        $data['date'] = $newArr;
        if(!$data){
            $data = [];
        }
        return $res = [
            'code' => '200',
            'data' => $data,
            'msg' => 'scenery'
        ];
    }

    // 跟团游
    private function gDetail($gcode)
    {
        $dd = date("Y-m-d", time());
        $dt = strtotime($dd);
        
        $where = array(
            'a.g_code'                      =>  array('eq',$gcode),
            'a.g_is_del'                    =>  array('neq', '1'),                  // 未删除
            'a.g_is_pass'                   =>  array('eq', '5'),                   // 5为上线
            'unix_timestamp(a.g_on_time)'   =>  array('elt', $dt),                  // 上线时间小于等于今天
            'unix_timestamp(a.g_d_time)'    =>  array('egt', $dt)                   // 下线时间大于等于今天
        );

        // 商品详情查询
        //$field = 'a.g_id id,a.g_code code,a.g_name name,a.g_m_tittle title,a.g_file image,a.g_service_phone phone,a.g_go_address address,b.g_ts ts,b.g_go_tick_info goPrice,b.g_ba_tick_info baPrice,b.g_zf_info zfPrice,b.g_routing routing';
        $data = M('group a')
                ->join('lf_group_info b on a.g_code =b.g_code')
                ->where($where)
                ->find();

        //判断是否无此数据
        if(empty($data)){
            return $res = array(
                'code' => '403',
                'data' => [],
                'msg'  => '无此数据，请重新搜索！'
            );
        };

        $data['g_play_spot']        =       json_decode($data['g_play_spot'], true);
        $data['g_file']             =       json_decode($data['g_file'], true);
        $data['g_yd_time']          =       json_decode($data['g_yd_time'], true);
        $data['g_service_phone']    =       json_decode($data['g_service_phone'], true);
        $data['g_ladder_refund']    =       json_decode($data['g_ladder_refund'], true);
        $data['g_venu']             =       json_decode($data['g_venu'], true);
        $data['g_routing']          =       json_decode($data['g_routing'], true);
        $data['g_all_info']         =       json_decode($data['g_all_info'], true);
        $data['g_child_all_info']   =       json_decode($data['g_child_all_info'], true);
        $data['g_littl_tran']       =       json_decode($data['g_littl_tran'], true);
        $data['g_dfc']              =       json_decode($data['g_dfc'], true);
        $data['g_no_tick']          =       json_decode($data['g_no_tick'], true);
        $data['g_no_bc']            =       json_decode($data['g_no_bc'], true);
        $data['g_ts_man']           =       json_decode($data['g_ts_man'], true);
        $data['g_team_food']        =       json_decode($data['g_team_food'], true);
        $data['g_no_team']          =       json_decode($data['g_no_team'], true);
        $data['g_zf_info']          =       json_decode($data['g_zf_info'], true);
        $data['g_service']          =       json_decode($data['g_service'], true);

        $data['g_l_tran'] = $data['g_l_tran'] == 'false' ? 1:0;
        
        foreach ($data['g_file'] as $k => $val) {
            $data['g_file'][$k]['src'] = C('img_url') . $val['src'];
        }

        //价格日历
        $where = [
            'g_is_open' => '1',                               // 库存开放
            'g_code' => array('eq',$gcode),
            'unix_timestamp(g_go_time)' => array('egt', $dt)  // 时间大于等于今天
        ];
       
        $date = M('group_price')
                ->where($where)
                ->select();
        //日历排序
        $date = $this->array_sort($date,'g_go_time');
        $newArr = $this->newDate($date,'g_go_time');
        $data['date'] = $newArr;
        if(!$data){
            $data = [];
        }
        return $res = [
            'code' => '200',
            'data' => $data,
            'msg' => 'group'
        ];
    }

    //日历排序
    private function array_sort($arr,$keys,$type='asc'){
        $keysvalue = $new_array = array();
        foreach ($arr as $k=>$v){
            $time = strtotime($v[$keys]);
            $date = date('Y-m-d',$time);
            $keysvalue[$k] = $v[$keys];
        }
        if($type == 'asc'){
            asort($keysvalue);
        }else{
            arsort($keysvalue);
        }
        reset($keysvalue);
        foreach ($keysvalue as $k=>$v){
            $new_array[$k] = $arr[$k];
        }
        return $new_array;
    }

    //日历重组
    private function newDate($date,$code){
        $newArr = array();
        $nowDate = date("Y-m-d", time());
        $nowMonth = substr($nowDate,5,2); //当前月份

        
        // 取最后一个时间和现在的月份的差
        if(empty($date)){
            $monthNum = 1;
        }else{
            $date2 = @array_slice($date,-1,1)[0][$code];
            $monthNum = @$this->getMonthNum($nowDate,$date2);
        }
        if($monthNum < 3){
            $monthNum = 3; 
        }
        $nowMonth = (int)$nowMonth - 1;
        // 输出三个月的价格
        for($j=0;$j<$monthNum;$j++){
            
            $nowMonth += 1;

            //超出12个月
            if($nowMonth >12){
                $nowMonth = (int)$nowMonth-12;
            }

            for($i=1;$i<32;$i++){
                $newArr[$j][$i] = '';
                foreach($date as $key=>$val){
                    if(substr($val[$code],5,2) == $nowMonth && substr($val[$code],8) == $i && strtotime($val[$code]) >= strtotime(date("Y-m-d", time()))){
                        $newArr[$j][$i] = $date[$key];
                    }
                }
            }
        }
        return $newArr;
    }

    // 计算两个时间 
    private function getMonthNum($date1,$date2){
        $date1_stamp = strtotime($date1);
        $date2_stamp = strtotime($date2);
        list($date_1['y'],$date_1['m']) = explode("-",date('Y-m',$date1_stamp));
        list($date_2['y'],$date_2['m']) = explode("-",date('Y-m',$date2_stamp));
        return abs($date_1['y']-$date_2['y'])*12 +$date_2['m']-$date_1['m'];
    }

    // 获取开始日期与结束日期之间所有日期
    private function getDateFromRange($startdate, $enddate){

        $stimestamp = strtotime($startdate);
        $etimestamp = strtotime($enddate);

        // 计算日期段内有多少天
        $days = ($etimestamp-$stimestamp)/86400+1;

        // 保存每天日期
        $date = array();

        for($i=0; $i<$days; $i++){
            $date[] = date('Y-m-d', $stimestamp+(86400*$i));
        }

        return $date;
    }
}