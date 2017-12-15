<?php
/**
 * Created by zhang.
 * User: zhang
 * Date: 2017/11/13
 * Time: 9:17
 */

namespace Home\Controller;


use Think\Controller;

class IndexController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!empty(($_SESSION['user_mobile']))) {
            $this ->assign('mobile',$_SESSION['user_mobile']);
        }
    }

    /**
     * 网站首页
     */
    public function index()
    {
        $page = rand(1, 10);

        $group = M('group')->field('lf_group.g_file,lf_group.g_code,lf_group.g_m_tittle,lf_group.g_go_address,lf_group.g_e_address,lf_group.g_play_spot,lf_group.g_name,lf_group.g_code,lf_group.g_user_code,lf_group_info.g_play_day')->join("lf_group_info on lf_group.g_code = lf_group_info.g_code")->limit($page, 10)->select();
        $seceny = M('scenery')->field('s_sell,s_tj_ly,s_tick_date,s_user_id,s_img,s_code,s_id,s_sj_time,s_xj_time,s_type,s_is_pass_log,s_name')->limit($page, 10)->select();
        $tickList = M('tick')
            ->field('t_category,t_tick_city,t_tick_my_price,(t_tick_my_price - t_tick_settle_price) as yj,t_tick_date,t_user_id,t_tick_spot,t_tick_cat,t_tick_name,t_tick_file,t_code')->limit($page, 10)->select();
        $this->assign('group', $group);
        $this->assign('tick', $tickList);
        $this->assign('seceny', $seceny);
        $this->display();
    }

    /**
     * 跟团游
     */
    public function groupList()
    {
        $this->display();
    }

    public function groupLi()
    {
        $page = I('post.page');
        if (empty($page)) {
            $page = 0;
        } else {
            $page--;
        }
        //出发地
        if (!empty(I('post.gAddress'))) {
            $go = I('post.gAddress');
            $where['g_go_address'] = array('like', "%$go%");
        }

        //目的地
        if (!empty(I('post.eAddress'))) {
            $e = I('post.eAddress');
            $where['g_go_address'] = array('like', "%$e%");
        }

        //景点
        if (!empty(I('post.spot'))) {
            $s = I('post.spot');
            $where['g_play_spot'] = array('like', "%$s%");
        }

        /**
         * 天数
         */
        if (!empty(I('post.day'))) {
            $day = I('post.day');
            if ($day != 10) {
                $where['lf_group_info.g_play_day'] = $day;
            } else {
                $where['lf_group_info.g_play_day'] = array('egt', $day);
            }
        }

        if (!empty(I('post.gad'))) {
            $gad = I('post.gad');
            $where['g_e_address'] = array('like', "%$gad%");
        }

        $where['g_is_del'] = array('neq', '1');
        $where['g_is_pass'] = array('eq', '5');
        $dd = date("Y-m-d", time());
        $dt = strtotime($dd);
        $where['unix_timestamp(g_on_time)'] = array('elt', $dt);
        $where['unix_timestamp(g_d_time)'] = array('egt', $dt);
        $tickList = M('group')->field('lf_group.g_file,lf_group.g_code,lf_group.g_m_tittle,lf_group.g_go_address,lf_group.g_e_address,lf_group.g_play_spot,lf_group.g_name,lf_group.g_code,lf_group.g_user_code,lf_group_info.g_play_day')->join("lf_group_info on lf_group.g_code = lf_group_info.g_code")->where($where)->order($order)->select();
        foreach ($tickList as &$t) {
            $img = json_decode($t['g_file'], true);
            foreach ($img as $i) {
                if ($i['head'] === 'true') {
                    $t['imgFile'] = C('img_url') . $i['src'];
                    break;
                }
            }
            if (empty($t['imgFile'])) {
                $t['imgFile'] = C('img_url') . $img[0]['src'];
            }
        }
        foreach ($tickList as &$tinfo) {
            $gprice = M('group_price')->field('min(g_man_my_price) as price')->where(array('g_code' => $tinfo['g_code'], 'g_user_code' => $tinfo['g_user_code']))->find();
            $tinfo['price'] = $gprice['price'];
            $tinfo['yj'] = $gprice['price'] - $gprice['g_man_js_price'];
        }
        if (!empty(I('post.ap')) && !empty(I('post.bp'))) {
            $ap = I('post.ap');
            $ep = I('post.ep');
            foreach ($tickList as $key => $tlst) {
                if ($ap != 1000) {
                    if ($tlst['price'] < $ap || $tlst['price'] > $ep) {
                        unset($tickList[$key]);
                    }
                } else {
                    if ($tlst['price'] < $ap) {
                        unset($tickList[$key]);
                    }
                }

            }
        }

        if (!empty(I('post.cbp')) && !empty(I('post.cep'))) {
            $ap = I('post.cbp');
            $ep = I('post.cep');
            foreach ($tickList as $key => $tlst) {
                if ($tlst['price'] < $ap || $tlst['price'] > $ep) {
                    unset($tickList[$key]);
                }
            }
        }

        if (!empty(I('post.xl'))) {
            foreach ($tickList as $key => $tl) {
                $sortArray[$key] = $tl['g_sell'];
            }
            array_multisort($sortArray, SORT_DESC, $tickList);
        }
        if (!empty(I('post.asprice'))) {
            foreach ($tickList as $key => $tl) {
                $sortArray[$key] = $tl['price'];
            }
            array_multisort($sortArray, SORT_ASC, $tickList);
        }
        if (!empty(I('post.desprice'))) {
            foreach ($tickList as $key => $tl) {
                $sortArray[$key] = $tl['price'];
            }
            array_multisort($sortArray, SORT_DESC, $tickList);
        }
        $this->assign('group', $tickList);
        $html = $this->display();
        return $html;
    }

    /**
     * 景就套餐
     */
    public function seceneyList()
    {
        $this->display();
    }

    public function seceneyLi()
    {
        $page = I('post.page');
        if (empty($page)) {
            $page = 0;
        } else {
            $page--;
        }

        //景区时间名称
        if (!empty(I('post.name'))) {
            $name = I('post.name');
            $vwhere['v_name'] = array('like', "%$name%");
            $code = M('view')->field('v_code')->where($vwhere)->select();
            $ids = [];
            foreach ($code as $item) {
                $ids[] = $item['v_code'];
            }
            if (!empty($code)) {
                $where['s_view'] = array('in', implode(',', $ids));
            }
        }

        if (!empty(I('post.hotelSearchInp'))) {
            $hi = I('post.hotelSearchInp');
            $where['s_name'] = array('like', "%$hi%");
        }

        if (!empty(I('post.allSearchInp'))) {
            $hi = I('post.allSearchInp');
            $where['s_name'] = array('like', "%$hi%");
        }
        if (!empty(I('post.adult'))) {
            $anum = I('post.adult');
            $where['s_man_num'] = $anum;
        }
        if (!empty(I('post.children'))) {
            $cnum = I('post.adult');
            $where['s_child_num'] = $cnum;
        }
        //几天 间夜
        if (!empty(I('post.day'))) {
            $where['s_hotel_day'] = I('post.day');
        }
        $where['s_is_del'] = array('neq', 1);
        $where['s_type'] = array('eq', 5);
        $dd = date("Y-m-d", time());
        $dt = strtotime($dd);
        $where['unix_timestamp(s_sj_time)'] = array('elt', $dt);
        $where['unix_timestamp(s_xj_time)'] = array('egt', $dt);
        $count = M('scenery')->where($where)->count();
        $list = M('scenery')->field('s_sell,s_tj_ly,s_tick_date,s_user_id,s_img,s_code,s_id,s_sj_time,s_xj_time,s_type,s_is_pass_log,s_name')->where($where)->order('s_create_time desc')->select();

        foreach ($list as &$ls) {
            $ls['s_tj_ly'] = json_decode($ls['s_tj_ly'], true);
            $ls['s_img'] = json_decode($ls['s_img'], true);
            foreach ($ls['s_img'] as &$sgl) {
                if ($sgl['headImg'] === 'true') {
                    $ls['imgfile'] = C('img_url') . $sgl['imgtitle'];
                    break;
                }
            }
            if (empty($ls['imgfile'])) {
                $ls['imgfile'] = C('img_url') . $ls['s_img'][0]['imgtitle'];
            }
            if ($ls['s_tick_date'] == 1) {
                $priceshow = M('scenery_yx')->field('max(unix_timestamp(y_b_time)) as bdate,min(unix_timestamp(y_b_time)) as edate,min(y_my_price) as price,y_mark_price,y_js_price ')->where(array('y_code' => $ls['s_code'], 'y_user_id' => $ls['s_user_id']))->group('y_code')->find();
                $ls['my_price'] = $priceshow['price'];
                $ls['yj'] = $priceshow['price'] - $priceshow['y_js_price'];
                $ls['mark_price'] = $priceshow['y_mark_price'];
                $ls['b_date'] = $priceshow['bdate'];
                $ls['b_date_e'] = $priceshow['b_date'] + $ls['s_hotel_day'] * 3600 * 24;
                $ls['e_date'] = $priceshow['edate'];
                $ls['e_date_e'] = $priceshow['edate'] + $ls['s_hotel_day'] * 3600 * 24;
            } else {
                $priceshow = M('seceny')->field('max(unix_timestamp(p_date)) as bdate,min(unix_timestamp(p_date)) as edate, min(p_my_price) as price ,p_mark_price,p_js_price')->where(array('p_code' => $ls['s_code'], 'p_user_code' => $ls['s_user_id']))->group('p_code')->find();
                $ls['my_price'] = $priceshow['price'];
                $ls['yj'] = $priceshow['price'] - $priceshow['p_js_price'];
                $ls['mark_price'] = $priceshow['p_mark_price'];
                $ls['b_date'] = $priceshow['bdate'];
                $ls['b_date_e'] = $priceshow['b_date'] + $ls['s_hotel_day'] * 3600 * 24;
                $ls['e_date'] = $priceshow['edate'];
                $ls['e_date_e'] = $priceshow['edate'] + $ls['s_hotel_day'] * 3600 * 24;;
            }
        }

        if (!empty(I('post.hotelBeginTime'))) {
            foreach ($list as $key => $dls) {
                if ($dls['s_tick_date'] == 1) {
                    $ttime = M('scenery_yx')->field('y_b_time')->where(array('y_code' => $dls['s_code'], 'y_user_id' => $dls['s_user_id']))->group('y_code')->select();
                    foreach ($ttime as $time) {
                        $tmpArray[] = strtotime($time['y_b_time']);
                    }
                    if (!in_array(strtotime(I('post.hotelBeginTime')), $tmpArray)) {
                        unset($list[$key]);
                    }
                } else {
                    $ttime = M('seceny')->field('p_date')->where(array('p_code' => $dls['s_code'], 'p_user_code' => $dls['s_user_id']))->group('p_code')->select();
                    foreach ($ttime as $time) {
                        $tmpArray[] = strtotime($time['p_date']);
                    }
                    if (!in_array(strtotime(I('post.hotelBeginTime')), $tmpArray)) {
                        unset($list[$key]);
                    }
                }
            }
        }
        if (!empty(I('post.hotelEndTime'))) {
            foreach ($list as $key => $dls) {
                if ($dls['s_tick_date'] == 1) {
                    $ttime = M('scenery_yx')->field('y_b_time')->where(array('y_code' => $dls['s_code'], 'y_user_id' => $dls['s_user_id']))->group('y_code')->select();
                    foreach ($ttime as $time) {
                        $tmpArray[] = strtotime($time['y_b_time']) + $dls['s_hotel_day'] * 3600 * 24;
                    }
                    if (!in_array(strtotime(I('post.hotelEndTime')), $tmpArray)) {
                        unset($list[$key]);
                    }
                } else {
                    $ttime = M('seceny')->field('p_date')->where(array('p_code' => $dls['s_code'], 'p_user_code' => $dls['s_user_id']))->group('p_code')->select();
                    foreach ($ttime as $time) {
                        $tmpArray[] = strtotime($time['p_date']) + $dls['s_hotel_day'] * 3600 * 24;
                    }
                    if (!in_array(strtotime(I('post.hotelEndTime')), $tmpArray)) {
                        unset($list[$key]);
                    }
                }
            }
        }

        if (!empty(I('post.ap')) && I('post.ap') != -1) {
            $ap = I('post.ap');
            $bp = I('post.bp');
            foreach ($list as $key => $ls) {
                if ($ap < 1001) {
                    if ($ls['my_price'] < $ap || $ls['my_price'] > $bp) {
                        unset($list[$key]);
                    }
                } else {
                    if ($ls['my_price'] < $ap) {
                        unset($list[$key]);
                    }
                }

            }

        }

        if (!empty(I('post.aprice'))) {
            foreach ($list as $key => $tl) {
                $sortArray[$key] = $tl['my_price'];
            }
            array_multisort($sortArray, SORT_ASC, $list);
        }
        if (!empty(I('post.dprice'))) {
            foreach ($list as $key => $tl) {
                $sortArray[$key] = $tl['my_price'];
            }
            array_multisort($sortArray, SORT_DESC, $list);
        }
        if (!empty(I('post.xl'))) {
            foreach ($list as $key => $tl) {
                $sortArray[$key] = $tl['s_sell'];
            }
            array_multisort($sortArray, SORT_DESC, $list);
        }
        $this->assign('seceney', $list);
        $html = $this->display();
        return $html;
    }

    /**
     * 景酒套餐详细信息
     */
    public function secenyDetail(){
        $this -> display();
    }
    /**
     * 门票
     */
    public function tickList()
    {
        $this->display();
    }

    public function tickLi()
    {
        $page = I('post.page');
        if (empty($page)) {
            $page = 0;
        } else {
            $page--;
        }
        //城市搜索
        if (!empty(I('post.ci'))) {
            $ci = I('post.ci');
            $where['t_tick_city'] = array('like', "%$ci%");
        }
        //景点
        if (!empty(I('post.spot'))) {
            $s = I('post.spot');
            $where['t_tick_name'] = array('like', "%$s%");
        }

        $where['t_tick_del'] = array('neq', '1');
        $where['t_tick_type'] = array('eq', '4');
        $dd = date("Y-m-d", time());
        $dt = strtotime($dd);
        $where['unix_timestamp(t_tick_sj_time)'] = array('elt', $dt);
        $where['unix_timestamp(t_tick_xj_time)'] = array('egt', $dt);
        $tickList = M('tick')
            ->field('t_category,t_tick_city,t_tick_my_price,(t_tick_my_price - t_tick_settle_price) as yj,t_tick_date,t_user_id,t_tick_spot,t_tick_cat,t_tick_name,t_tick_file,t_code')
            ->where($where)->select();
        foreach ($tickList as &$t) {
            $img = json_decode($t['t_tick_file'], true);
            foreach ($img as $im) {
                if ($im['head'] === 'true') {
                    $t['imgFile'] = C('img_url') . $im['src'];
                    break;
                }
            }
            if (empty($t['imgFile'])) {
                $t['imgFile'] = C('img_url') . $img[0]['src'];
            }
        }
        foreach ($tickList as &$i) {
            if ($i['t_tick_date'] == 1) {
                $info = M('tick_y')->field('y_b_time,y_e_time')->where(array('y_code' => $i['t_code'], 'y_user_code' => $i['t_user_id']))->find();
                $i['yj'] = $i['t_tick_my_price'] - $i['t_tick_settle_price'];
                $i['b_date'] = strtotime($info['y_b_time']);
                $i['e_date'] = strtotime($info['y_e_time']);
            } elseif ($i['t_tick_date'] == 2) {
                $info = M('tick_price')->field('max(unix_timestamp(p_date)) bdate,min(unix_timestamp(p_date)) edate,p_mark_price,p_js_price,min(p_my_price) as price')->where(array('p_code' => $i['t_code'], 'p_user_code' => $i['t_user_id']))->group('p_code')->find();
                $i['t_tick_my_price'] = $info['price'];
                $i['p_mark_price'] = $info['p_mark_price'];
                $i['yj'] = $info['price'] - $info['p_js_price'];
                $i['b_date'] = $info['bdate'];
                $i['e_date'] = $info['edate'];
            }
        }
        if (!empty(I('post.desprice'))) {

            foreach ($tickList as $key => $tl) {
                $sortArray[$key] = $tl['t_tick_my_price'];
            }
            array_multisort($sortArray, SORT_DESC, $tickList);
        }
        if (!empty(I('post.ascprice'))) {
            foreach ($tickList as $key => $tl) {
                $sortArray[$key] = $tl['t_tick_my_price'];
            }
            array_multisort($sortArray, SORT_ASC, $tickList);
        }
        if (!empty(I('post.xl'))) {
            foreach ($tickList as $key => $tl) {
                $sortArray[$key] = $tl['t_p_sell'];
            }
            array_multisort($sortArray, SORT_DESC, $tickList);
        }

        if (!empty(I('post.bTime'))) {
            foreach ($tickList as $key => $one) {
                if ($one['b_date'] < strtotime(I('post.bTime'))) {
                    unset($tickList[$key]);
                }
            }
        }
        if (!empty(I('post.eTime'))) {
            foreach ($tickList as $key => $one) {
                if ($one['b_date'] > strtotime(I('post.eTime'))) {
                    unset($tickList[$key]);
                }
            }
        }
        if (!empty(I('post.one'))) {
            foreach ($tickList as $key => $one) {
                if ($one['t_tick_my_price'] > 100) {
                    unset($tickList[$key]);
                }
            }
        }
        if (!empty(I('post.two'))) {
            foreach ($tickList as $key => $one) {

                if ($one['t_tick_my_price'] < 100 || $one['t_tick_my_price'] > 500) {
                    unset($tickList[$key]);
                }
            }
        }
        if (!empty(I('post.three'))) {
            foreach ($tickList as $key => $one) {
                if ($one['t_tick_my_price'] < 500 || $one['t_tick_my_price'] > 1000) {
                    unset($tickList[$key]);
                }
            }
        }
        if (!empty(I('post.five'))) {
            foreach ($tickList as $key => $one) {
                if ($one['t_tick_my_price'] > 1000) {
                    unset($tickList[$key]);
                }
            }
        }

        if (!empty(I('post.bp')) && !empty(I('post.ep'))) {
            foreach ($tickList as $key => $one) {
                if ($one['t_tick_my_price'] < I('post.bp') || $one['t_tick_my_price'] > I('post.ep')) {
                    unset($tickList[$key]);
                }
            }
        }
        $this->assign('tick', $tickList);
        $html = $this->display();
        return $html;
    }

    public function demo(){
        #todo 测试
//        $this->display("Tourist/p_detail");
        $re=M('sp')->find();
        var_dump($re);
    }
}