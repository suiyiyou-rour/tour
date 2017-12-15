<?php
/**
 *  列表显示接口
 */
namespace Weixin\Controller;
class ListController extends BaseController {
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
            ->limit(3)
            ->select();

        $this->HeadImg($group,"g_file");    // 处理首图
        $this->AddMark($group,"group");     // 添加标记
//        var_dump(M('group')->_sql());
//        var_dump($group);

        // 景酒套餐 时间在上线区间 type上线标记5 未删除标记0 销量降序 创建订单时间降序
//        $s_where['s_is_del']                    =  array('neq', 1);
//        $s_where['s_type']                      =  array('eq', 5);
//        $s_where['unix_timestamp(s_sj_time)']   =  array('elt', $dt);
//        $s_where['unix_timestamp(s_xj_time)']   =  array('egt', $dt);
//
//        $scenery = M('scenery')
//                    ->field('s_id as id,s_code as code,s_name as name,s_img,s_sell as sell')
//                    ->where($s_where)
//                    ->order('s_sell desc ,s_create_time desc')
//                    ->limit(2)
//                    ->select();
//        $this->GroupHeadImg($scenery,"s_img");    // 处理首图
//        $this->AddMark($scenery,"scenery");  // 添加标记
//        var_dump(M('scenery')->_sql());
//        var_dump($scenery);


        // 门票 时间在上线区间 type上线标记4 未删除标记0 销量降序 id升序
        $t_where['t_tick_del']                        =  array('neq', '1');
        $t_where['t_tick_type']                       =  array('eq', '4');
        $t_where['unix_timestamp(t_tick_sj_time)']  =  array('elt', $dt);
        $t_where['unix_timestamp(t_tick_xj_time)']  =  array('egt', $dt);

        $tick = M('tick')
            ->field('t_id as id,t_code as code,t_tick_name as name,t_tick_file,t_tick_sell as sell')
            ->where($t_where)
            ->order('t_tick_sell desc ,t_id asc')
            ->limit(3)
            ->select();
        $this->HeadImg($tick,"t_tick_file");    // 处理首图
        $this->AddMark($tick,"tick");           // 添加标记
//        var_dump(M('tick')->_sql());
//        var_dump($tick);

        // 数组合并
        if(!$group){
            $group=[];
        }
//        if(!$scenery){
//            $scenery=[];
//        }
        if(!$tick){
            $tick=[];
        }
        $list = array_merge($group,$tick);
//        var_dump($list);
//        print_r($list);

        // 数组按销量排序 id降序
        foreach ($list as $key => $row)
        {
            $AllSell[$key]  = $row['sell'];
            $Allld[$key]    = $row['id'];
        }
        array_multisort($AllSell, SORT_DESC, $Allld, SORT_ASC, $list);

//        print_r($list);
//        exit;
        $Returndata["code"]=200;
        if($list){
            $Returndata["data"]=$list;
        }else{
            $Returndata["data"]=array();
        }

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
            ->field("g_id as id,g_name as name,g_file,g_code as code,g_user_code,g_sell as sell")
            ->order("g_sell desc , g_id asc")
            ->limit($page * 10, 10)
            ->select();
        if(!$group){
            $this->ajaxReturn(array("code"=>200,"data"=>array()));
        }
        $this->HeadImg($group,"g_file");  // 处理首图
        $this->AddMark($group,"group");   // 添加标记


        //判断是不是分销商自己
        $jsremark = $this->checkJS();

        // 处理价格和佣金
        foreach ($group as &$tinfo) {
            $gpWhere["g_code"]                          =       $tinfo['code'];             //产品编号
            $gpWhere["g_user_code"]                     =       $tinfo['g_user_code'];     //供应商编号
//            $gpWhere["unix_timestamp(g_go_time)"]      =       array('EGT', $dt);         //出发时间
            //商品最低价格
            $gprice = M("group_price")->field('min(g_man_my_price) as price')->group("g_code")->where($gpWhere)->find();
            if(!$gprice){
                $this->ajaxReturn(array("code"=>200,"msg"=>"跟团游价格出错"));
            }

            if($jsremark){
                $gpriceInfo = M("group_price")->field('g_man_my_price as price,g_man_js_price')->where($gpWhere)->where("g_man_my_price =".$gprice["price"])->find();
                if(!$gprice){
                    $this->ajaxReturn(array("code"=>200,"msg"=>"跟团游价格出错"));
                }
                $tinfo['js_price']   =   $gpriceInfo['g_man_js_price'];                        //结算价格
                $tinfo['yj']      =   $gpriceInfo['price'] - $gpriceInfo['g_man_js_price'];    //佣金
            }
            $tinfo['price']   =   $gprice['price'];                                              //价格

        }
//        if($group == null){
//            $group = [];
//        }
        $this->ajaxReturn(array("code" => 200 , "data" =>$group));

    }

    // 门票列表
    public function TickList(){
        //todo 佣金
        $page = I('page');
        if (empty($page)) {
            $page = 0;
        } else {
            $page--;
        }
        $search=I('search');
        if($search){
            $t_where['t_tick_name|t_tick_cat|t_tick_spot'] = array('like', "%$search%");
        }

        $dd                                             =  date("Y-m-d", time());
        $dt                                             =  strtotime($dd);
        $t_where['t_tick_del']                         =  array('neq', '1');
        $t_where['t_tick_type']                        =  array('eq', '4');
        $t_where['unix_timestamp(t_tick_sj_time)']   =  array('elt', $dt);
        $t_where['unix_timestamp(t_tick_xj_time)']   =  array('egt', $dt);
        $tick = M('tick')
            ->field('t_id as id,t_code as code,t_tick_name as name,t_tick_file,t_tick_sell as sell,t_tick_date,t_user_id,t_tick_my_price,t_tick_settle_price,t_tick_cat,t_tick_spot')
            ->where($t_where)
            ->order('t_tick_sell desc ,t_id asc')
            ->limit($page * 10, 10)
            ->select();
        if(!$tick){
            $this->ajaxReturn(array("code" => 200 , "data" =>array()));
        }
        $this->HeadImg($tick,"t_tick_file");    // 处理首图
        $this->AddMark($tick,"tick");           // 添加标记

        //判断是不是分销商自己
        $jsremark = $this->checkJS();

        foreach ($tick as &$i) {
            if ($i['t_tick_date'] == 1) {
                $i['price']         =   $i['t_tick_my_price'];                                  //平台价格
                if($jsremark){
                    $i['js_price']     =   $i['t_tick_settle_price'];                              //结算价格
                    $i['yj']            =   $i['t_tick_my_price'] - $i['t_tick_settle_price'];      //佣金
                }

            } else{
                $infoWhere["p_code"]                      =          $i['code'];                //code
                $infoWhere["p_user_code"]                 =         $i['t_user_id'];            //供应商code
//                $infoWhere["unix_timestamp(p_date)"]     =          array('EGT', $dt);         //出发时间
                //
                $minPrice = M('tick_price')->field('min(p_my_price) as price') ->where($infoWhere)->group('p_code')->find();
                if($minPrice){
                    if($jsremark){
                        $tickInfo = M("tick_price")->field('p_js_price,min(p_my_price) as price')->where($infoWhere)->where("p_my_price =".$minPrice["price"])->find();
                        $i['js_price']    =  $tickInfo['p_js_price'];                           //结算价格
                        $i['yj']             =  $tickInfo['price'] - $tickInfo['p_js_price'];   //佣金
                    }
                    $i['price']         =  $minPrice["price"];                                  //价格
                }else{
                    //todo 删除 没有价格那一条
                }
//                $this->ajaxReturn(array("code"=>200,"msg"=>M('tick_price')->_sql()));
//                exit();
//                if(!$minPrice){
//                    $this->ajaxReturn(array("code"=>200,"msg"=>"门票价格出错"));
//                }


            }
        }
//        if($tick == null){
//            $tick =[];
//        }
//        var_dump($tick);
//        exit();
        $this->ajaxReturn(array("code" => 200 , "data" =>$tick));

    }

    // 酒景套餐列表
    public function SceneryList(){
        return;
        //todo 佣金计算有问题 2017.12.07 后调整
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

//        print_r($scenery);
//        exit();
        $Returndata["code"]  = 200;
        if($scenery == null){
            $scenery =[];
        }
        $Returndata["data"]  = $scenery;
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

    //判断自己是不是经销商
    public function checkJS(){
        $openid = session('openid');
        $jsPid = cookie('pid');
        $jsremark = false;
        if($openid && $jsPid){
            $jsarray["user_id"] = $jsPid;
            $jsarray["user_is_lx"] = $jsPid;
            $jsarray["user_wx_code"] = $openid;
            $jsarray["user_type"] = 2;
            $jsre=M('user')->field('user_id')->where($jsarray)->find();
            if($jsre){
                $jsremark = true;
            }
        }
        return $jsremark;
    }
}
