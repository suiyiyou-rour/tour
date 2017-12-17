<?php
/**
 *  页面以及 列表接口
 */
namespace Page\Controller;
use Think\Controller;
class IndexController extends Controller {
    /*主页面显示*/
    public function index(){
        //todo 搜索没做
        $user = session('user_online');
        $this->assign('login',$user['code']);
        $this->display('index/home');
    }

    //注册
    public function register(){
        $this->display('index/register');
    }
    // 登录
    public function login(){
        $this->display('index/login');
    }

    // 分销商注册
    public function fregister(){
        $this->display('index/fregister');
    }
    //首页
    public function home(){
        
        $this->display('index/home');
    }

    //跟团游详情
    public function p_route(){
        $this->display('index/p_route');
    }

    //门票搜索
    public function s_ticket(){
        $this->display('index/s_ticket');
    }

    //跟团游搜索
    public function s_route(){
        $this->display('index/s_route');
    }

    //酒店搜索
    public function s_hotel(){
        $this->display('index/s_hotel');
    }

    //跟团游 游客信息
    public function d_route(){
        $this->display('index/d_route');
    }


    //跟团游支付
    public function route_pay(){
        $this->display('index/route_pay');
    }

    public function ticket_pay(){
        $this->display('index/ticket_pay');
    }

    //订单管理
    public function order(){
        $this->display('index/order');
    }

    public function p_ticket(){
        $this->display('index/p_ticket');
    }

    public function d_ticket(){
        $this->display('index/d_ticket');
    }

    

    // 首页推荐列表
    public function RecommendList(){
        //todo 表里没东西查询字段先注释 三种各两个 销量降序 id升序

        // 跟团游 时间在上线区间 上线标记5 未删除标记0 销量降序 id升序 重命名方便遍历
        $dd = date("Y-m-d", time());
        $dt = strtotime($dd);

        $g_where['g_is_del']                    =  array('neq', '1');
        $g_where['g_is_pass']                   =  array('eq', '5');
        $g_where['unix_timestamp(g_on_time)']   =  array('elt', $dt);
        $g_where['unix_timestamp(g_d_time)']    =  array('egt', $dt);
        
        $group = M('group')
                ->where($g_where)
                ->field("g_id as id,g_name as name,g_file,g_code as code,g_sell as sell")
                ->order("g_sell desc , g_id asc")
                ->limit(2)
                ->select();

        $this->HeadImg($group,"g_file");    // 处理首图
        $this->AddMark($group,"group");     // 添加标记
//        var_dump(M('group')->_sql());
//        var_dump($group);

        // 景酒套餐 时间在上线区间 type上线标记5 未删除标记0 销量降序 创建订单时间降序
        $s_where['s_is_del']                    =  array('neq', 1);
        $s_where['s_type']                      =  array('eq', 5);
        $s_where['unix_timestamp(s_sj_time)']   =  array('elt', $dt);
        $s_where['unix_timestamp(s_xj_time)']   =  array('egt', $dt);
        
        $scenery = M('scenery')
                    ->field('s_id as id,s_code as code,s_name as name,s_img,s_sell as sell')
                    ->where($s_where)
                    ->order('s_sell desc ,s_create_time desc')
                    ->limit(2)
                    ->select();
        $this->GroupHeadImg($scenery,"s_img");    // 处理首图
        $this->AddMark($scenery,"scenery");  // 添加标记
//        var_dump(M('scenery')->_sql());


        // 门票 时间在上线区间 type上线标记4 未删除标记0 销量降序 id升序
        $t_where['t_tick_del']                        =  array('neq', '1');
        $t_where['t_tick_type']                       =  array('eq', '4');
        $t_where['unix_timestamp(t_tick_sj_time)']  =  array('elt', $dt);
        $t_where['unix_timestamp(t_tick_xj_time)']  =  array('egt', $dt);

        $tick = M('tick')
            ->field('t_id as id,t_code as code,t_tick_name as name,t_tick_file,t_tick_sell as sell')
            ->where($t_where)
            ->order('t_tick_sell desc ,t_id asc')
            ->limit(2)
            ->select();
        $this->HeadImg($tick,"t_tick_file");    // 处理首图
        $this->AddMark($tick,"tick");           // 添加标记
//        var_dump(M('tick')->_sql());
//        var_dump($tick);

        // 数组合并
        $list = array_merge($group,$scenery,$tick);
//        print_r($list);

        // 数组按销量排序 id降序
        foreach ($list as $key => $row)
        {
            $AllSell[$key]  = $row['sell'];
            $Allld[$key]    = $row['id'];
        }
        array_multisort($AllSell, SORT_DESC, $Allld, SORT_ASC, $list);

//        print_r($list);
        $Returndata["code"]=200;
        $Returndata["data"]=$list;
        $this->ajaxReturn($Returndata);
    }

    // 首页轮播
    public function DynamicFigure(){
        // 查询门票3
        $dd = date("Y-m-d", time());
        $dt = strtotime($dd);

        // 门票 时间在上线区间 type上线标记4 未删除标记0 销量降序 id升序
        $t_where['t_tick_del']                     = array('neq', '1');
        $t_where['t_tick_type']                    = array('eq', '4');
        $t_where['unix_timestamp(t_tick_sj_time)'] = array('elt', $dt);
        $t_where['unix_timestamp(t_tick_xj_time)'] = array('egt', $dt);
        
        $tick = M('tick')
                ->field('t_id as id,t_code as code,t_tick_file')
                ->where($t_where)
                ->order('t_tick_sell desc ,t_id asc')
                ->limit(3)
                ->select();

        $this->HeadImg($tick,"t_tick_file");   // 处理首图
        $this->AddMark($tick,"tick");          // 添加标记
//        var_dump(M('tick')->_sql());
//        print_r($tick);
        $Returndata["code"] = 200;
        $Returndata["data"] = $tick;
        $this->ajaxReturn($Returndata);
    }

    // 首页模糊搜索全部
    public function AllFuzzySearch(){

    }

    // 跟团游列表
    public function GroupList(){
        $page = (int)I('page');
        if (empty($page)) {
            $page = 0;
        } else {
            $page--;
        }
        $search=I('search');
        if($search){
            $g_where['g_name']=array('like', "%$search%");
        }
        $dd  =  date("Y-m-d", time());
        $dt  =  strtotime($dd);

        $g_where['g_is_del']                    =  array('neq', '1');
        $g_where['g_is_pass']                   =  array('eq', '5');
        $g_where['unix_timestamp(g_on_time)']   =  array('elt', $dt);
        $g_where['unix_timestamp(g_d_time)']    =  array('egt', $dt);

        $group = M('group')
                ->where($g_where)
                ->field("g_id as id,g_name as name,g_file,g_code as code,g_user_code,g_sell as sell,g_tick_type")
                ->order("g_sell desc , g_id asc")
                ->limit($page * 10, 10)
                ->select();
        $this->HeadImg($group,"g_file");  // 处理首图
        $this->AddMark($group,"group");   // 添加标记

        // 处理价格和佣金
        foreach ($group as &$tinfo) {
            $gprice = M('group_price')
                        ->field('min(g_man_my_price) as price,g_man_js_price')
                        ->where(array('g_code' => $tinfo['code'], 'g_user_code' => $tinfo['g_user_code']))
                        ->find();
            $tinfo['price']   =   $gprice['price'];
            $tinfo['yj']      =   $gprice['price'] - $gprice['g_man_js_price'];
        }

        $Returndata["code"]  =  200;
        $Returndata["data"]  =  $group;
        $this->ajaxReturn($Returndata);
    }

    // 酒景套餐列表
    public function SceneryList(){
        $page = I('page');

        if (empty($page)) {
            $page = 0;
        } else {
            $page--;
        }
        $search=I('search');
        if($search){
            $s_where['s_name']=array('like', "%$search%");
        }

        $dd = date("Y-m-d", time());
        $dt = strtotime($dd);

        $s_where['s_is_del']                    =   array('neq', 1);
        $s_where['s_type']                      =   array('eq', 5);
        $s_where['unix_timestamp(s_sj_time)']   =   array('elt', $dt);
        $s_where['unix_timestamp(s_xj_time)']   =   array('egt', $dt);

        $scenery = M('scenery')
                    ->field('s_id as id,s_code as code,s_name as name,s_img,s_sell as sell,s_user_id,s_tick_date')
                    ->where($s_where)
                    ->order('s_sell desc ,s_create_time desc')
                    ->limit($page * 10, 10)
                    ->select();
        $this->GroupHeadImg($scenery,"s_img");    // 处理首图
        $this->AddMark($scenery,"scenery");  // 添加标记

        foreach ($scenery as &$ls) {
            if ($ls['s_tick_date'] == 1) {
                $priceshow = M('scenery_yx')->field('min(y_my_price) as price,y_mark_price,y_js_price ')
                            ->where(array('y_code' => $ls['code'], 'y_user_id' => $ls['s_user_id']))
                            ->group('y_code')
                            ->find();
                $ls['price']     =  $priceshow['price'];
                $ls['yj']           =  $priceshow['price'] - $priceshow['y_js_price'];
                $ls['mark_price']   =  $priceshow['y_mark_price'];
//                echo M('scenery_yx')->_sql()."<br/>";
            } else {
                $priceshow = M('seceny_price')
                            ->field('min(p_my_price) as price ,p_mark_price,p_js_price')
                            ->where(array('p_code' => $ls['code'], 'p_user_code' => $ls['s_user_id']))
                            ->group('p_code')
                            ->find();
                $ls['price'] = $priceshow['price'];
                $ls['yj'] = $priceshow['price'] - $priceshow['p_js_price'];
                $ls['mark_price'] = $priceshow['p_mark_price'];
            }
        }
//        exit();
//        print_r($scenery);
        $Returndata["code"]  = 200;
        $Returndata["data"]  = $scenery;
        $this->ajaxReturn($Returndata);
    }

    // 门票列表
    public function TickList(){
        $page = I('page');
        if (empty($page)) {
            $page = 0;
        } else {
            $page--;
        }
        $search=I('search');
        if($search){
            $s_where['t_tick_name']=array('like', "%$search%");
        }

        $dd                                          =  date("Y-m-d", time());
        $dt                                          =  strtotime($dd);
        $t_where['t_tick_del']                       =  array('neq', '1');
        $t_where['t_tick_type']                      =  array('eq', '4');
        $t_where['unix_timestamp(t_tick_sj_time)']   =  array('elt', $dt);
        $t_where['unix_timestamp(t_tick_xj_time)']   =  array('egt', $dt);
        $tick = M('tick')
                ->field('t_id as id,t_code as code,t_tick_name as name,t_tick_file,t_tick_sell as sell,t_tick_date,t_user_id,t_tick_my_price,t_tick_settle_price,t_tick_cat,t_tick_spot')
                ->where($t_where)
                ->order('t_tick_sell desc ,t_id asc')
                ->select();

        $this->HeadImg($tick,"t_tick_file");    // 处理首图
        $this->AddMark($tick,"tick");           // 添加标记

        foreach ($tick as &$i) {
            if ($i['t_tick_date'] == 1) {
                $i['yj'] = $i['t_tick_my_price'] - $i['t_tick_settle_price'];
                $i['price'] = $i['t_tick_my_price'];
            } else{
                $info = M('tick_price')
                        ->field('p_mark_price,p_js_price,min(p_my_price) as price')
                        ->where(array('p_code' => $i['code'], 'p_user_code' => $i['t_user_id']))
                        ->group('p_code')
                        ->find();
                $i['price']         =  $info['price'];
                $i['mark_price']    =  $info['p_mark_price'];
                $i['yj']            =  $info['price'] - $info['p_js_price'];
            }
        }
        $Returndata["code"] = 200;
        $Returndata["data"] = $tick;
        $this->ajaxReturn($Returndata);

    }

    // 处理首图
    public function HeadImg(&$list,$name){
        foreach ($list as &$val) {
            $img = json_decode($val[$name], true);
            foreach ($img as $i) {
                if ($i['head'] === 'true') {
                    $val['imgFile'] = C('img_url') . $i['src'];
                    break;
                }
            }
            if (empty($val['imgFile'])) {
                $val['imgFile'] = C('img_url') . $img[0]['src'];
            }
            unset($val[$name]);
        }
        return $list;
    }

    // 处理首图
    public function GroupHeadImg(&$list,$name){
        foreach ($list as &$val) {
            $img = json_decode($val[$name], true);
            foreach ($img as $i) {
                if ($i['headImg'] === 'true') {
                    $val['imgFile'] = C('img_url') . $i['imgtitle'];
                    break;
                }
            }
            if (empty($val['imgFile'])) {
                $val['imgFile'] = C('img_url') . $img[0]['imgtitle'];
            }
            unset($val[$name]);
        }
        return $list;
    }

    // 添加标记
    public function AddMark(&$list,$name){
        foreach ($list as &$val){
            $val["shop_type"] = $name;
        }
        return $list;
    }

}
