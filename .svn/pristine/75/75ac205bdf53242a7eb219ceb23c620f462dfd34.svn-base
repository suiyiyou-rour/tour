<?php
/**
 * Created by zhang.
 * User: zhang
 * Date: 2017/10/23
 * Time: 10:30
 */

namespace Home\Controller;


use Think\Controller;

class UserProductController extends Controller
{


    /**
     * 获取所有门票产品
     */
    public function getTickList()
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
            $s = I('post.ci');
            $where['t_tick_spot'] = array('like', "%$s%");
        }
        $where['t_tick_del'] = array('neq', '1');
        $where['t_tick_type'] = array('eq', '4');
        $dd = date("Y-m-d", time());
        $dt = strtotime($dd);
        $where['unix_timestamp(t_tick_sj_time)'] = array('elt', $dt);
        $where['unix_timestamp(t_tick_xj_time)'] = array('egt', $dt);
        $tickList = M('tick')
            ->field('t_category,t_tick_city,t_tick_my_price,(t_tick_my_price - t_tick_settle_price) as yj,t_tick_date,t_user_id,t_tick_spot,t_tick_cat,t_tick_name,t_tick_file,t_code')
            ->where($where)->limit($page * 10, 10)->select();
        foreach ($tickList as &$t) {
            $img = json_decode($t['t_tick_file'], true);
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
        foreach ($tickList as &$i) {
            if ($i['t_tick_date'] == 1) {
                $i['yj'] = $i['t_tick_my_price'] - $i['t_tick_settle_price'];
            } elseif ($i['t_tick_date'] == 2) {
                $info = M('tick_price')->field('p_mark_price,p_js_price,min(p_my_price) as price')->where(array('p_code' => $i['t_code'], 'p_user_code' => $i['t_user_id']))->group('p_code')->find();
                $i['t_tick_my_price'] = $info['price'];
                $i['p_mark_price'] = $info['p_mark_price'];
                $i['yj'] = $info['price'] - $info['p_js_price'];
            }
        }
        $this->ajaxReturn($tickList);
    }

    /**
     * 获取门票产品的详细信息
     */
    public function getProductInfo()
    {
        $code = I('post.code');
        $userCode = I('post.userCode');
        $rinfo = [];
        if (empty($code)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '请提交产品Id'));
        }
        $tickInfo = M('tick')->where('t_user_id =' . $userCode . " and t_code = " . $code)->find();
        $c = M('contact')->field('c_name,c_id')->where(array('c_id' => $tickInfo['t_contract']))->find();
        $tickInfo['c_name'] = $c['c_name'];
        $tickInfo['t_tick_cost'] = json_decode($tickInfo['t_tick_cost'], true);
        $tickInfo['t_tick_file'] = json_decode($tickInfo['t_tick_file'], true);
        $tickInfo['t_tickService_num'] = json_decode($tickInfo['t_tickService_num'], true);
        $tickInfo['t_tick_use_address'] = json_decode($tickInfo['t_tick_use_address'], true);
        $tickInfo['t_tick_playerInfo'] = json_decode($tickInfo['t_tick_playerInfo'], true);
        $tickInfo['t_tick_pre_book_time'] = json_decode($tickInfo['t_tick_pre_book_time'], true);
        $tickInfo['t_tick_identity'] = json_decode($tickInfo['t_tick_identity'], true);
        foreach ($tickInfo['t_tick_file'] as &$im) {
            $im['src'] = C('img_url') . $im['src'];
        }
        if ($tickInfo['t_tick_playerInfo']['num']['disabled'] === 'false') {
            $tickInfo['t_tick_playerInfo']['num']['disabled'] = false;
        } else {
            $tickInfo['t_tick_playerInfo']['num']['disabled'] = true;
        }
        if ($tickInfo['t_tick_playerInfo']['errTip'] === 'false') {
            $tickInfo['t_tick_playerInfo']['errTip'] = false;
        } else {
            $tickInfo['t_tick_playerInfo']['errTip'] = true;
        }

        foreach ($tickInfo['t_tick_playerInfo']['identityInfo'] as &$titi) {
            if ($titi === 'false') {
                $titi = false;
            } else {
                $titi = true;
            }
        }

        if ($tickInfo['t_tick_playerInfo']['errTip'] === 'false') {
            $tickInfo['t_tick_playerInfo']['errTip'] = false;
        } else {
            $tickInfo['t_tick_playerInfo']['errTip'] = true;
        }

        if ($tickInfo['t_tick_pre_book_time']['two']['hour']['disabled'] === 'false') {
            $tickInfo['t_tick_pre_book_time']['two']['hour']['disabled'] = false;
        } else {
            $tickInfo['t_tick_pre_book_time']['two']['hour']['disabled'] = true;
        }

        if ($tickInfo['t_tick_pre_book_time']['two']['minute']['disabled'] === 'false') {
            $tickInfo['t_tick_pre_book_time']['two']['minute']['disabled'] = false;
        } else {
            $tickInfo['t_tick_pre_book_time']['two']['minute']['disabled'] = true;
        }
        if ($tickInfo['t_tick_pre_book_time']['two']['textshow'] === 'false') {
            $tickInfo['t_tick_pre_book_time']['two']['textshow'] = false;
        } else {
            $tickInfo['t_tick_pre_book_time']['two']['textshow'] = true;
        }

        if ($tickInfo['t_tick_pre_book_time']['three']['hour']['disabled'] === 'false') {
            $tickInfo['t_tick_pre_book_time']['three']['hour']['disabled'] = false;
        } else {
            $tickInfo['t_tick_pre_book_time']['three']['hour']['disabled'] = true;
        }
        if ($tickInfo['t_tick_pre_book_time']['three']['day']['disabled'] === 'false') {
            $tickInfo['t_tick_pre_book_time']['three']['day']['disabled'] = false;
        } else {
            $tickInfo['t_tick_pre_book_time']['three']['day']['disabled'] = true;
        }

        if ($tickInfo['t_tick_pre_book_time']['three']['minute']['disabled'] === 'false') {
            $tickInfo['t_tick_pre_book_time']['three']['minute']['disabled'] = false;
        } else {
            $tickInfo['t_tick_pre_book_time']['three']['minute']['disabled'] = true;
        }
        if ($tickInfo['t_tick_pre_book_time']['three']['textshow'] === 'false') {
            $tickInfo['t_tick_pre_book_time']['three']['textshow'] = false;
        } else {
            $tickInfo['t_tick_pre_book_time']['three']['textshow'] = true;
        }

        if ($tickInfo['t_tick_identity']['errTip'] === 'false') {
            $tickInfo['t_tick_identity']['errTip'] = false;
        } else {
            $tickInfo['t_tick_identity']['errTip'] = true;
        }


        foreach ($tickInfo['t_tick_playerInfo']['papersInfo'] as &$tpi) {
            if ($tpi === 'false') {
                $tpi = false;
            } else {
                $tpi = true;
            }
        }
        $tickInfo['t_yd_num'] = json_decode($tickInfo['t_yd_num'], true);

        $tickInfo['t_go_b_time'] = json_decode($tickInfo['t_go_b_time'], true);
        foreach ($tickInfo['t_go_b_time'] as &$tgbt) {
            if ($tgbt['bcInfo']['bool'] === 'false') {
                $tgbt['bcInfo']['bool'] = false;
            } else {
                $tgbt['bcInfo']['bool'] = true;
            }
        }
        if ($tickInfo['t_yd_num']['errTip'] === 'false') {
            $tickInfo['t_yd_num']['errTip'] = false;
        } else {
            $tickInfo['t_yd_num']['errTip'] = true;
        }
        $tickInfo['enter_time'] = $tickInfo['t_go_b_time'];
        if ($tickInfo['t_tick_date'] == 1) {
            $result = M('tick_y')->where(array('y_code' => $code, 'y_user_code' => $userCode))->find();
            $canUseTime = json_decode($result['y_can_use_time'], true);
            $canWeek = [];
            $noUseTime = json_decode($result['y_no_user_time'], true);
            foreach ($canUseTime as $key => $ci) {
                if ($ci === 'true') {
                    switch ($key) {
                        case '0' :
                            $canWeek[] = '周一';
                            break;
                        case '1' :
                            $canWeek[] = '周二';
                            break;
                        case '2' :
                            $canWeek[] = '周三';
                            break;
                        case '3' :
                            $canWeek[] = '周四';
                            break;
                        case '4' :
                            $canWeek[] = '周五';
                            break;
                        case '5' :
                            $canWeek[] = '周六';
                            break;
                        case '6' :
                            $canWeek[] = '周日';
                            break;
                    }
                }
            }

            $dateInfo = $this->getDateFromRange($result['y_b_time'], $result['y_e_time']);
            foreach ($dateInfo as $item) {
                $tinfo = strtotime($item);
                $y = date("Y", $tinfo);
                $m = date('m', $tinfo);
                $key = $this->mFristAndLast($y, $m);
                if (!in_array($item, $noUseTime) && in_array($this->getTimeWeek($tinfo), $canWeek)) {
                    $idata['p_date'] = $item;
                    $idata['sor_p_date'] = $tinfo;
                    $idata['p_my_price'] = $tickInfo['t_tick_my_price'];
                    $idata['yj'] = $tickInfo['t_tick_my_price'] - $tickInfo['p_js_price'];
                    $idata['week'] = $this->getTimeWeek($tinfo);
                    if ($tinfo <= time()) {
                        $idata['flag'] = false;
                    } else {
                        $idata['flag'] = true;
                    }

                    $rinfo[$key][] = $idata;
                }
            }
        } elseif ($tickInfo['t_tick_date'] == 2) {
            $result = M('tick_price')->field('p_date,p_my_price,p_js_price')->where(array('p_code' => $code, 'p_user_code' => $userCode, 'p_is_open' => 1))->select();
            foreach ($result as $r) {
                $tinfo = strtotime($r['p_date']);
                $y = date("Y", $tinfo);
                $m = date('m', $tinfo);
                $key = $this->mFristAndLast($y, $m);
                $week = $this->getTimeWeek($tinfo);
                $r['week'] = $week;
                $r['sor_p_date'] = $tinfo;
                $r['yj'] = $r['p_my_price'] - $r['p_js_price'];
                if ($tinfo <= time()) {
                    $r['flag'] = false;
                } else {
                    $r['flag'] = true;
                }
                $rinfo[$key][] = $r;
            }
        }
        $returnDate = array();
        foreach ($rinfo as $key => &$tr) {
            $dayArr = $this->get_day($key);
            foreach ($tr as &$td) {
                $haveDay[] = $td['p_date'];
            }
            $d = array_diff($dayArr, $haveDay);
            $tdArry = [];
            foreach ($d as $i => $tdd) {
                $tdArry[$i]['sor_p_date'] = strtotime($tdd);
                $tdArry[$i]['week'] = $this->getTimeWeek(strtotime($tdd));
                $tdArry[$i]['p_date'] = $tdd;
                $tdArry[$i]['p_my_price'] = null;
                $tdArry[$i]['yj'] = null;
                $tdArry[$i]['flag'] = false;
            }
            $c = array_merge($tr, $tdArry);

            $ages = array();
            foreach ($c as $user) {
                $ages[] = $user['sor_p_date'];
            }
            array_multisort($ages, SORT_ASC, $c);
            switch ($c[0]['week']) {
                case '周一':
                    $cd = 1;
                    break;
                case '周二':
                    $cd = 2;
                    break;
                case '周三':
                    $cd = 3;
                    break;
                case '周四':
                    $cd = 4;
                    break;
                case '周五':
                    $cd = 5;
                    break;
                case '周六':
                    $cd = 6;
                    break;
                case '周日':
                    $cd = 0;
                    break;
            }
            for ($i = 0; $i < $cd; $i++) {
                $wiht[] = 1;
            }
            foreach ($c as &$dc) {
                $tec = explode('-', $dc['p_date']);
                $dc['p_date'] = $tec['2'];

            }
            $tmp['date'] = $key;
            $tmp['val'] = $c;
            $tmp['cd'] = $wiht;
            unset($wiht);
            $returnDate[] = $tmp;
        }
        if (empty($tickInfo)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '不存在该产品'));
        }
        $return['tickInfo'] = $tickInfo;
        $return['rl'] = $returnDate;

        $this->ajaxReturn($return);
    }

    /**
     * 获取当月天数
     * @param $date
     * @param $rtype 1天数 2具体日期数组
     * @return
     */
    public function get_day($date, $rtype = '2')
    {
        $tem = explode('-', $date);    //切割日期 得到年份和月份
        $year = $tem['0'];
        $month = $tem['1'];
        if (in_array($month, array(1, 3, 5, 7, 8, 01, 03, 05, 07, 08, 10, 12))) {
            // $text = $year.'年的'.$month.'月有31天';
            $text = '31';
        } elseif ($month == 2) {
            if ($year % 400 == 0 || ($year % 4 == 0 && $year % 100 !== 0))    //判断是否是闰年
            {
                // $text = $year.'年的'.$month.'月有29天';
                $text = '29';
            } else {
                // $text = $year.'年的'.$month.'月有28天';
                $text = '28';
            }
        } else {
            // $text = $year.'年的'.$month.'月有30天';
            $text = '30';
        }
        if ($rtype == '2') {
            for ($i = 1; $i <= $text; $i++) {
                $r[] = $year . "-" . $month . "-" . $i;
            }
        } else {
            $r = $text;
        }
        return $r;
    }

    public function mFristAndLast($y = "", $m = "")
    {
        if ($y == "") $y = date("Y");
        if ($m == "") $m = date("m");
        $m = sprintf("%02d", intval($m));
        $y = str_pad(intval($y), 4, "0", STR_PAD_RIGHT);

        $m > 12 || $m < 1 ? $m = 1 : $m = $m;
        $firstday = strtotime($y . $m . "01000000");
        $firstdaystr = date("Y-m-01", $firstday);
        $lastday = strtotime(date('Y-m-d 23:59:59', strtotime("$firstdaystr +1 month -1 day")));

        return date("Y-m", $firstday);
    }

    public function getTimeWeek($time, $i = 0)
    {
        $weekarray = array("日", "一", "二", "三", "四", "五", "六");
        $oneD = 24 * 60 * 60;
        return "周" . $weekarray[date("w", $time + $oneD * $i)];
    }

    /**
     * 获取指定日期段内每一天的日期
     * @param Date $startdate 开始日期
     * @param Date $enddate 结束日期
     * @return Array
     */
    function getDateFromRange($startdate, $enddate)
    {
        $stimestamp = strtotime($startdate);
        $etimestamp = strtotime($enddate);
        // 计算日期段内有多少天
        $days = ($etimestamp - $stimestamp) / 86400 + 1;
        // 保存每天日期
        $date = array();
        for ($i = 0; $i < $days; $i++) {
            $date[] = date('Y-m-d', $stimestamp + (86400 * $i));
        }
        return $date;
    }

    /**
     * 获取所有跟团游
     */

    public function getGroupList()
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

        $where['g_is_del'] = array('neq', '1');
        $where['g_is_pass'] = array('eq', '5');
        $dd = date("Y-m-d", time());
        $dt = strtotime($dd);
        $where['unix_timestamp(g_on_time)'] = array('elt', $dt);
        $where['unix_timestamp(g_d_time)'] = array('egt', $dt);
        $tickList = M('group')->field('g_file,g_code,g_m_tittle,g_go_address,g_e_address,g_play_spot,g_name,g_code,g_user_code')->where($where)->select();
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
        $this->ajaxReturn($tickList);

    }

    /**
     * 获取跟团有详细信息
     */
    public function getGroupInfo()
    {
        $code = I('code');
        $userCode = I('userCode');
        $baseInfo = M('group')->where(array('g_code' => $code, 'g_user_code' => $userCode))->find();
        $baseInfo['g_service'] = json_decode($baseInfo['g_service'], true);
        $baseInfo['g_file'] = json_decode($baseInfo['g_file'], true);
        $baseInfo['g_yd_time'] = json_decode($baseInfo['g_yd_time'], true);
        $baseInfo['g_ladder_refund'] = json_decode($baseInfo['g_ladder_refund'], true);
        $baseInfo['g_play_spot'] = json_decode($baseInfo['g_play_spot'], true);
        $baseInfo['g_service_phone'] = json_decode($baseInfo['g_service_phone'], true);

        $groupInfo = M('group_info')->where(array('g_code' => $code, 'g_user_id' => $userCode))->find();
        $groupInfo['g_venu'] = json_decode($groupInfo['g_venu'], true);
        $groupInfo['g_routing'] = json_decode($groupInfo['g_routing'], true);
        $groupInfo['g_ts'] = json_decode($groupInfo['g_ts'], true);
        $groupInfo['g_all_info'] = json_decode($groupInfo['g_all_info'], true);
        $groupInfo['g_zf_info'] = json_decode($groupInfo['g_zf_info'], true);
        $groupInfo['g_child_all_info'] = json_decode($groupInfo['g_child_all_info'], true);
        $groupInfo['g_littl_tran'] = json_decode($groupInfo['g_littl_tran'], true);
        $groupInfo['g_dfc'] = json_decode($groupInfo['g_dfc'], true);
        $groupInfo['g_no_tick'] = json_decode($groupInfo['g_no_tick'], true);
        $groupInfo['g_no_tick_info'] = json_decode($groupInfo['g_no_tick_info'], true);
        $groupInfo['g_no_bc'] = json_decode($groupInfo['g_no_bc'], true);
        $groupInfo['g_ts_man'] = json_decode($groupInfo['g_ts_man'], true);
        $groupInfo['g_team_food'] = json_decode($groupInfo['g_team_food'], true);
        $groupInfo['g_no_team'] = json_decode($groupInfo['g_no_team'], true);
        $groupPrice = M('group_price')->where(array('g_code' => $code, 'g_user_code' => $userCode, 'g_is_open' => 1))->select();
        $gprice = M('group_price')->field('min(g_man_my_price) as price')->where(array('g_code' => $code, 'g_user_code' => $userCode))->find();
        $groupInfo['price'] = $gprice['price'];
        foreach ($groupPrice as $r) {
            $tinfo = strtotime($r['g_go_time']);
            $y = date("Y", $tinfo);
            $m = date('m', $tinfo);
            $key = $this->mFristAndLast($y, $m);
            $week = $this->getTimeWeek($tinfo);
            $r['week'] = $week;
            $r['yj'] = $r['g_man_my_price'] - $r['g_man_js_price'];
            $r['sor_p_date'] = $tinfo;
            if ($tinfo <= time()) {
                $r['flag'] = false;
            } else {
                $r['flag'] = true;
            }
            $rinfo[$key][] = $r;
        }

        if ($groupInfo['g_l_tran'] === 'false') {
            $groupInfo['g_l_tran'] = false;
        } else {
            $groupInfo['g_l_tran'] = true;
        }

        if ($groupInfo['g_bx'] === 'false') {
            $groupInfo['g_bx'] = false;
        } else {
            $groupInfo['g_bx'] = true;
        }


//        $returnDate = array();
//        foreach ($rinfo as $key => &$rid) {
//            $tmp['date'] = $key;
//            foreach ($rid as &$gdfch) {
//                if ($gdfch['g_df_ch'] === 'false') {
//                    $gdfch['g_df_ch'] = false;
//                } else {
//                    $gdfch['g_df_ch'] = true;
//                }
//                if ($gdfch['g_is_buy'] === 'false') {
//                    $gdfch['g_is_buy'] = false;
//                } else {
//                    $gdfch['g_is_buy'] = true;
//                }
//            }
//
//            $tmp['val'] = $rid;
//            $returnDate[] = $tmp;
//        }
        foreach ($rinfo as $key => &$tr) {
            $dayArr = $this->get_day($key);
            foreach ($tr as &$td) {
                $haveDay[] = $td['g_go_time'];
            }
            foreach ($tr as &$tdprcd) {
                $tdprcd['p_date'] = $tdprcd['g_go_time'];
                $tdprcd['p_js_price'] = $tdprcd['g_go_time'];
            }
            $d = array_diff($dayArr, $haveDay);
            $tdArry = [];
            foreach ($d as $i => $tdd) {
                $tdArry[$i]['sor_p_date'] = strtotime($tdd);
                $tdArry[$i]['week'] = $this->getTimeWeek(strtotime($tdd));
                $tdArry[$i]['p_date'] = $tdd;
                $tdArry[$i]['flag'] = false;
            }
            $c = array_merge($tr, $tdArry);

            $ages = array();
            foreach ($c as $user) {
                $ages[] = $user['sor_p_date'];
            }
            array_multisort($ages, SORT_ASC, $c);

            $tmp['date'] = $key;
            switch ($c[0]['week']) {
                case '周一':
                    $cd = 1;
                    break;
                case '周二':
                    $cd = 2;
                    break;
                case '周三':
                    $cd = 3;
                    break;
                case '周四':
                    $cd = 4;
                    break;
                case '周五':
                    $cd = 5;
                    break;
                case '周六':
                    $cd = 6;
                    break;
                case '周日':
                    $cd = 0;
                    break;
            }
            for ($i = 0; $i < $cd; $i++) {
                $wiht[] = 1;
            }
            foreach ($c as &$dc) {
                $tec = explode('-', $dc['p_date']);
                $dc['p_date'] = $tec['2'];
                if($dc['g_df_ch'] === 'true'){
                    $dc['g_df_ch'] = true;
                }else{
                    $dc['g_df_ch'] = false;
                }
            }
            $tmp['cd'] = $wiht;
            $tmp['val'] = $c;
            unset($wiht);
            $returnDate[] = $tmp;
        }
        foreach ($baseInfo['g_play_spot'] as &$gs) {
            if ($gs['selected'] === 'false') {
                $gs['selected'] = false;
            } else {
                $gs['selected'] = true;
            }
            if ($gs['disabled'] === 'false') {
                $gs['disabled'] = false;
            } else {
                $gs['disabled'] = true;
            }
        }
        foreach ($baseInfo['g_service'] as &$gsv) {
            if ($gsv === 'true') {
                $gsv = true;
            } else {
                $gsv = false;
            }
        }
        foreach ($baseInfo['g_file'] as &$gfl) {
            $gfl['src'] = C('img_url') . $gfl['src'];
            if ($gfl['headImg'] === 'true') {
                $gfl['headImg'] = true;
            } else {
                $gfl['headImg'] = false;
            }

        }
        foreach ($baseInfo['g_ladder_refund'] as &$glre) {
            if ($glre['wymoney']['val'] === 'true') {
                $glre['wymoney']['val'] = true;
            } else {
                $glre['wymoney']['val'] = false;
            }

            if ($glre['disabled'] === 'false') {
                $glre['disabled'] = false;
            } else {
                $glre['disabled'] = true;
            }
        }

        foreach ($groupInfo['g_routing'] as &$grt) {
            foreach ($grt['food'] as &$f) {
                if ($f['bool'] === 'true') {
                    $f['bool'] = true;
                } else {
                    $f['bool'] = false;
                }
            }
        }
        foreach ($groupInfo['g_littl_tran'] as &$gltr) {
            if ($gltr === 'true') {
                $gltr = true;
            } else {
                $gltr = false;
            }
        }
        foreach ($groupInfo['g_no_tick'] as &$gntc) {
            if ($gntc === 'true') {
                $gntc = true;
            } else {
                $gntc = false;
            }
        }
        foreach ($groupInfo['g_no_bc'] as &$gnbc) {
            if ($gnbc === 'true') {
                $gnbc = true;
            } else {
                $gnbc = false;
            }
        }
        foreach ($groupInfo['g_ts_man'] as &$gtsm) {
            if (empty($gtsm['val'])) {
                if ($gtsm === 'true') {
                    $gtsm = true;
                } else {
                    $gtsm = false;
                }
            } else {
                if ($gtsm['val'] === 'true') {
                    $gtsm['val'] = true;
                } else {
                    $gtsm['val'] = false;
                }
            }
        }
        if ($groupInfo['g_bkkl'] === 'true') {
            $groupInfo['g_bkkl'] = true;
        } else {
            $groupInfo['g_bkkl'] = false;
        }
        if ($groupInfo['g_team_food']['val'] === 'true') {
            $groupInfo['g_team_food']['val'] = true;
        } else {
            $groupInfo['g_team_food']['val'] = false;
        }
        $groupInfo['g_anothe_book_info'] = json_decode($groupInfo['g_anothe_book_info'], true);


        foreach ($groupInfo['g_no_team'] as &$gntm) {
            if ($gntm === 'true') {
                $gntm = true;
            } else {
                $gntm = false;
            }
        }
        foreach ($groupInfo['g_dfc'] as &$gdfc) {
            if ($gdfc === 'true') {
                $gdfc = true;
            } else {
                $gdfc = false;
            }
        }
        foreach ($groupInfo['g_child_all_info'] as &$gcail) {
            if ($gcail['zc'] === 'true') {
                $gcail['zc'] = true;
            } else {
                $gcail['zc'] = false;
            }

            if ($gcail['djt'] === 'true') {
                $gcail['djt'] = true;
            } else {
                $gcail['djt'] = false;
            }
            if ($gcail['car'] === 'true') {
                $gcail['car'] = true;
            } else {
                $gcail['car'] = false;
            }
            if ($gcail['tick'] === 'true') {
                $gcail['tick'] = true;
            } else {
                $gcail['tick'] = false;
            }
            if ($gcail['guider'] === 'true') {
                $gcail['guider'] = true;
            } else {
                $gcail['guider'] = false;
            }
            if ($gcail['dinner'] === 'true') {
                $item['dinner'] = true;
            } else {
                $gcail['dinner'] = false;
            }
            if ($gcail['halfpricedinner'] === 'true') {
                $gcail['halfpricedinner'] = true;
            } else {
                $gcail['halfpricedinner'] = false;
            }
        }

        $return['baseInfo'] = $baseInfo;
        $return['groupInfo'] = $groupInfo;
        $return['rl'] = $returnDate;
        $this->ajaxReturn($return);
    }


    /**
     * 获取所有景酒套餐
     */
    public function getViewFoodList()
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
        $list = M('scenery')->field('s_tick_date,s_user_id,s_img,s_code,s_id,s_sj_time,s_xj_time,s_type,s_is_pass_log,s_name')->where($where)->order('s_create_time desc')->select();

        foreach ($list as &$ls) {
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
                $priceshow = M('scenery_yx')->field('min(y_my_price) as price,y_mark_price,y_js_price ')->where(array('y_code' => $ls['s_code'], 'y_user_id' => $ls['s_user_id']))->group('y_code')->find();
                $ls['my_price'] = $priceshow['price'];
                $ls['yj'] = $priceshow['price'] - $priceshow['y_js_price'];
                $ls['mark_price'] = $priceshow['y_mark_price'];
            } else {
                $priceshow = M('seceny')->field('min(p_my_price) as price ,p_mark_price,p_js_price')->where(array('p_code' => $ls['s_code'], 'p_user_code' => $ls['s_user_id']))->group('p_code')->find();
                $ls['my_price'] = $priceshow['price'];
                $ls['yj'] = $priceshow['price'] - $priceshow['p_js_price'];
                $ls['mark_price'] = $priceshow['p_mark_price'];
            }
        }

        $return['info'] = $list;
        $this->ajaxReturn($return);
    }

    /**
     *
     * 景酒套餐详细信息
     */
    public function getViewInfoOne()
    {
        $code = I('code');
        $userCode = I('userCode');
        $baseInfo = M('scenery')->where(array('s_user_id' => $userCode, 's_code' => $code))->find();
        $baseInfo['s_hotel_t_info'] = json_decode($baseInfo['s_hotel_t_info'], true);
        $baseInfo['s_img'] = json_decode($baseInfo['s_img'], true);
        $baseInfo['s_view'] = json_decode($baseInfo['s_view'], true);
        $baseInfo['s_food'] = json_decode($baseInfo['s_food'], true);
        $baseInfo['s_tj_ly'] = json_decode($baseInfo['s_tj_ly'], true);
        $baseInfo['s_tag'] = json_decode($baseInfo['s_tag'], true);
        foreach ($baseInfo['s_img'] as &$big) {
            $big['imgtitle'] = C('img_url') . $big['imgtitle'];
            if ($big['headImg'] === 'false') {
                $big['headImg'] = false;
            } else {
                $big['headImg'] = true;
            }
        }
        if ($baseInfo['s_available'] === 'false') {
            $baseInfo['s_available'] = false;
        } else {
            $baseInfo['s_available'] = true;
        }
        foreach ($baseInfo['s_hotel_t_info'] as &$shtio) {
            if ($shtio['hotelListshow'] === 'true') {
                $shtio['hotelListshow'] = true;
            } else {
                $shtio['hotelListshow'] = false;
            }

            if ($shtio['roomListshow'] === 'true') {
                $shtio['roomListshow'] = true;
            } else {
                $shtio['roomListshow'] = false;
            }
        }

        foreach ($baseInfo['s_tag'] as &$st) {
            if ($st['bool'] === 'false') {
                $st['bool'] = false;
            } else {
                $st['bool'] = true;
            }

            if ($st['disabled'] === 'false') {
                $st['disabled'] = false;
            } else {
                $st['disabled'] = true;
            }
        }

        foreach ($baseInfo['s_hotel_t_info'] as &$h) {
            if ($h['hotelListshow'] === 'false') {
                $h['hotelListshow'] = false;
            } else {
                $h['hotelListshow'] = true;
            }
            if ($h['roomListshow'] === 'false') {
                $h['roomListshow'] = false;
            } else {
                $h['roomListshow'] = true;
            }
            $hotleInfo = M('hotel')->where(array('h_code' => $h['hotelcode'], 'h_user_id' => $userCode))->find();
            $h['h_city'] = $hotleInfo['h_city'];
            $h['h_name'] = $hotleInfo['h_name'];
            $h['h_address'] = $hotleInfo['h_address'];
            $h['h_check_time'] = $hotleInfo['h_check_time'];
            $h['h_out_time'] = $hotleInfo['h_out_time'];
            $h['h_prove'] = $hotleInfo['h_prove'];
            $h['h_introduction'] = $hotleInfo['h_introduction'];
            $h['h_des'] = $hotleInfo['h_des'];
            $h['h_other_info'] = $hotleInfo['h_other_info'];
            $img = json_decode($hotleInfo['h_img'], true);
            foreach ($img as &$ig) {
                foreach ($ig['srcArr'] as &$hsrimg) {
                    $hsrimg['src'] = C('img_url') . $hsrimg['src'];
                }
                if ($ig['addImgshow'] === 'false') {
                    $ig['addImgshow'] = false;
                } else {
                    $ig['addImgshow'] = true;
                }
            }
            $h['img'] = $img;
        }

        foreach ($baseInfo['s_view'] as &$s) {
            $vwInfo = M('view')->where(array('v_code' => $s['code'], 'v_user_id' => $userCode))->find();
            $vwInfo['v_img'] = json_decode($vwInfo['v_img'], true);
            foreach ($vwInfo['v_img'] as &$vimg) {
                foreach ($vimg['srcArr'] as &$sarr) {
                    $sarr['src'] = C('img_url') . $sarr['src'];
                    if ($sarr['addImgshow'] === 'false') {
                        $sarr['addImgshow'] = false;
                    } else {
                        $sarr['addImgshow'] = true;
                    }
                }
                if ($vimg['addImgshow'] === 'fasle') {
                    $vimg['addImgshow'] = false;
                } else {
                    $vimg['addImgshow'] = true;
                }
            }
            $s['info'] = $vwInfo;
            $vtick = M('view_tick')->where(array('v_tick_code' => $s['code'], 'v_user_id' => $userCode))->select();
            foreach ($vtick as &$vtt) {
                if ($vtt['v_tick_is'] === '0') {
                    $vtt['v_tick_is_show1'] = true;
                    $vtt['v_tick_is_show2'] = false;
                } else {
                    $vtt['v_tick_is_show1'] = false;
                    $vtt['v_tick_is_show2'] = true;
                }
//                if($vtt['v_tick_down_show'] === 'true'){
//                    $vtt['v_tick_down_show'] = 1;
//                }else{
//                    $vtt['v_tick_down_show'] =0;
//                }
            }

            $s['viewTick'] = $vtick;
        }

        foreach ($baseInfo['s_food'] as &$sfod) {
            $sfodInfo = M('food')->where(array('f_code' => $sfod['code'], 'f_user_id' => $userCode))->find();
            $sfodInfo['f_img'] = json_decode($sfodInfo['f_img'], true);

            foreach ($sfodInfo['f_img'] as &$fimg) {
                foreach ($fimg['srcArr'] as &$fra) {
                    $fra['src'] = C('img_url') . $fra['src'];
                }
                $fimg['imgdescribe'] = $fimg['des'];
                if ($fimg['addImgshow'] === 'false') {
                    $fimg['addImgshow'] = false;
                } else {
                    $fimg['addImgshow'] = true;
                }
            }
            $sfod['info'] = $sfodInfo;
            $foodinfo = M('food_use')->where(array('f_code' => $sfod['code'], 'f_user_id' => $userCode))->select();
            foreach ($foodinfo as &$finfo) {
                if ($finfo['f_use_is'] === '0') {
                    $finfo['f_use_is_show1'] = true;
                    $finfo['f_use_is_show2'] = false;
                } else {
                    $finfo['f_use_is_show1'] = false;
                    $finfo['f_use_is_show2'] = true;
                }
//                if($vtt['v_tick_down_show'] === 'true'){
//                    $vtt['v_tick_down_show'] = 1;
//                }else{
//                    $vtt['v_tick_down_show'] =0;
//                }
            }
            $sfod['foodUse'] = $foodinfo;
        }

        if ($baseInfo['s_tick_date'] = 1) {
            $result = M('scenery_yx')->where(array('y_code' => $code, 'y_user_id' => $userCode))->select();
            foreach ($result as $r) {
                $tinfo = strtotime($r['y_b_time']);
                $y = date("Y", $tinfo);
                $m = date('m', $tinfo);
                $key = $this->mFristAndLast($y, $m);
                $week = $this->getTimeWeek($tinfo);
                $r['week'] = $week;
                $r['yj'] = $r['y_my_price'] - $r['y_js_price'];
                $r['sor_p_date'] = $tinfo;
                if ($tinfo <= time()) {
                    $r['flag'] = false;
                } else {
                    $r['flag'] = true;
                }
                $rinfo[$key][] = $r;
            }

//            $returnDate = array();
//            foreach ($rinfo as $key => $rid) {
//                $tmp['date'] = $key;
//                $tmp['val'] = $rid;
//                $returnDate[] = $tmp;
//            }
        } else {
            $result = M('seceny_price')->where(array('p_user_code' => $userCode, 'p_code' => $code))->select();
            foreach ($result as $r) {
                $tinfo = strtotime($r['p_date']);
                $y = date("Y", $tinfo);
                $m = date('m', $tinfo);
                $key = $this->mFristAndLast($y, $m);
                $week = $this->getTimeWeek($tinfo);
                $r['week'] = $week;
                $r['yj'] = $r['p_my_price'] - $r['p_js_price'];
                $r['sor_p_date'] = $tinfo;
                if ($tinfo <= time()) {
                    $r['flag'] = false;
                } else {
                    $r['flag'] = true;
                }
                $rinfo[$key][] = $r;
            }

//            $returnDate = array();
//            foreach ($rinfo as $key => $rid) {
//                $tmp['date'] = $key;
//                $tmp['val'] = $rid;
//                $returnDate[] = $tmp;
//            }

        }
        foreach ($rinfo as $key => &$tr) {
            $dayArr = $this->get_day($key);
            if ($baseInfo['s_tick_date'] = 1) {
                foreach ($tr as &$td) {
                    $haveDay[] = $td['y_b_time'];
                }

                foreach ($tr as &$dprc) {
                    $dprc['p_date'] = $dprc['y_b_time'];
                    $dprc['p_js_price'] = $dprc['y_js_price'];
                    $dprc['p_my_price'] = $dprc['y_my_price'];
                    $dprc['p_mark_price'] = $dprc['y_mark_price'];
                }
            } else {
                foreach ($tr as &$td) {
                    $haveDay[] = $td['p_date'];
                }
            }
            $d = array_diff($dayArr, $haveDay);
            $tdArry = [];
            foreach ($d as $i => $tdd) {
                $tdArry[$i]['sor_p_date'] = strtotime($tdd);
                $tdArry[$i]['week'] = $this->getTimeWeek(strtotime($tdd));
                $tdArry[$i]['p_date'] = $tdd;
                $tdArry[$i]['flag'] = false;
            }
            $c = array_merge($tr, $tdArry);

            $ages = array();
            foreach ($c as $user) {
                $ages[] = $user['sor_p_date'];
            }
            array_multisort($ages, SORT_ASC, $c);

            $tmp['date'] = $key;
            switch ($c[0]['week']) {
                case '周一':
                    $cd = 1;
                    break;
                case '周二':
                    $cd = 2;
                    break;
                case '周三':
                    $cd = 3;
                    break;
                case '周四':
                    $cd = 4;
                    break;
                case '周五':
                    $cd = 5;
                    break;
                case '周六':
                    $cd = 6;
                    break;
                case '周日':
                    $cd = 0;
                    break;
            }
            for ($i = 0; $i < $cd; $i++) {
                $wiht[] = 1;
            }
            foreach ($c as &$dc) {
                $tec = explode('-', $dc['p_date']);
                $dc['p_date'] = $tec['2'];
            }
            $tmp['cd'] = $wiht;
            $tmp['val'] = $c;

            $returnDate[] = $tmp;
        }
        $areturn['info'] = $baseInfo;
        $areturn['data'] = $returnDate;
        $this->ajaxReturn($areturn);
    }
}