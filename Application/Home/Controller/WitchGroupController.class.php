<?php
/**
 * Created by zhang.
 * User: zhang
 * Date: 2017/10/21
 * Time: 14:01
 */

namespace Home\Controller;


class WitchGroupController extends BaseController
{
    public function newGroup()
    {
        $contact = M('contact')->where('c_user_id=' . $this->userId)->select();
        $this->ajaxReturn($contact);
    }

    /**
     * 添加跟团游产品基本信息保存
     */
    public function addGroupBaseInfo()
    {
        $data['g_contact_id'] = I('post.contact');//合同编号
        $code = I('post.code');
        if (empty($code)) {
            $data['g_code'] = $this->createCode();//产品编号
        }
        $data['g_n_code'] = I('ncode');//供应商产品编号
        $data['g_name'] = I('post.name');//供应商产品名称
        $data['g_m_tittle'] = I('post.mtittle');//美团产品副标题
        $data['g_service'] = json_encode(I('post.service'));//服务保障1 100% 2 无购物 3无续费
        $data['g_line'] = '1';//1国内
        $data['g_play'] = '1';//1跟团游
        $data['g_go_address'] = I('post.gaddress');//出发地
        $data['g_e_address'] = I('post.eaddress');//目的地
        $data['g_play_spot'] = json_encode(I('post.scenic'));//主要景点
        $tickImg = I('post.goodsImg');
        $path = "./Public/group/";
        foreach ($tickImg as $k => $t) {
            $imgf = $this->addr($t['src'], $path, $k);
            if (empty($imgf)) {
                $arr = parse_url($t['src']);
                $file[$k]['src'] = $arr['path'];
            } else {
                $file[$k]['src'] = $imgf;
            }
            $file[$k]['headImg'] = $t['headImg'];
        }
        $data['g_file'] = json_encode($file);
        $data['g_yd_time'] = json_encode(I('post.yTime'));//预定时间
        $data['g_on_time'] = I('post.oTime');//上线时间 审核通过上线为1
        $data['g_d_time'] = I('post.dTime');//下线时间 卖完自动下架为1
        $data['g_service_phone'] = json_encode(I('post.serviceNum'));//客服电弧
        $data['g_batch'] = I('post.batch');//1 支持部分退款  2 整单退款
        $data['g_ladder_refund'] = json_encode(I('post.lrefund'));//阶梯退款
        $data['g_rate'] = I('post.rate');//费率
        $data['g_is_pass'] = 4;//审核状态 1待审核 2 审核通过 3 驳回 4 制作中 5上线 6 下线
        //审核状态 1待审核 2 审核通过 3 驳回 4 制作中 5上线 6 下线
        //审核状态 1待审核 2 审核通过 3 驳回 4 制作中 5上线 6 下线
        //产品审核状态 1 待审核 2 已通过 3 已驳回 4 上线 5 保存  6下线
        $rl = I('post.rl');
        if (empty($code)) {
            $data['g_user_code'] = $this->userId;//商户编号
            $result = M('group')->add($data);
            if (!$result) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '保存失败'));
            }

            $this->improveGroupInfo($data['g_code']);

            $this->addPriceCa($rl, $data['g_code']);
        } else {
            $result = M('group')->where(array('g_user_code' => $this->userId, 'g_code' => $code))->save($data);
            if (!$result) {
                $this->ajaxReturn(array('code' => 0, 'msg' => '更新失败'));
            }
            $this->improveGroupInfo($code);
            $this->addPriceCa($rl, $code);
        }


        $this->ajaxReturn(array('code' => '1', 'msg' => '操作成功'));

    }

    /**
     * 网上跟团游详细信息
     */
    public function improveGroupInfo($code)
    {
        M('group_info')->where(array('g_user_id' => $this->userId, 'g_code' => $code))->delete();
        $data['g_code'] = $code;
        $data['g_anothe_book_info'] = json_encode(I('post.anotherBookInfo'));
        $data['g_play_day'] = I('post.day');//游玩天数
        $data['g_go_tran'] = I('post.gtran');//去程交通方式
        $data['g_back_tran'] = I('post.btran');//反程交通方式
        $data['g_go_tick_info'] = I('post.gtinfo');//去程费用
        $data['g_ba_tick_info'] = I('post.btinfo');//返回费用说明
        $data['g_venu'] = json_encode(I('post.vnue'));//集合地点
        $data['g_routing'] = json_encode(I('post.routing'));//行程安排
        $data['g_ts'] = I('post.ts');//产品特色
        $data['g_all_info'] = json_encode(I('post.xxinfo'));//详细信息
        $data['g_zf_info'] = json_encode(I('post.zinfo'));//自费项目
        $data['g_l_tran'] = I('post.ltran');//景区小交通 1 有 0 无
        $data['g_stay'] = I('post.stay');//1 无信息 2 行程 3 自定义
        $data['g_food'] = I('post.food');//1 无 2 行程（含飞机） 3行程（不含飞机） 4 含部分 5 全程不含
        $data['g_tick'] = I('post.tick');//1全部景点 2 不含 3 含部分
        $data['g_tick_info'] = I('post.tInfo');//包含的景点门票
        $data['g_tour_guider'] = I('post.tguider');//1 无 信息 2 中问导游 3 全程陪同 4全程和当地中问导游
        $data['g_bx'] = I('post.bx');//1 含保险 2 不含
        $data['g_child_info'] = I('post.cinfo');//1无信息 2 年龄 3 身高
        $data['g_child_all_info'] = json_encode(I('post.ainfo'));//儿童具体信息
        $data['g_bc_info'] = I('post.binfo');//补充说明
        $data['g_zs'] = I('post.zs');//赠送
        $data['g_another'] = I('post.another');//其他
        $data['g_bkkl'] = I('post.bkkl');//不可抗力 1选中。2 不选中
        $data['g_littl_tran'] = json_encode(I('post.nltran'));//是否包含小交通 1,1 1,0 ,01
        $data['g_dfc'] = json_encode(I('post.dfc'));//单房差
        $data['g_no_tick'] = json_encode(I('post.ntick'));//门票不包含
        $data['g_no_tick_info'] = I('post.ntinfo');//门票
        $data['g_no_bc'] = json_encode(I('post.nbc'));//补充
        $data['g_no_qt'] = I('post.ntq');//其他
        $data['g_ts_man'] = json_encode(I('post.tsman'));//特使人群
        $data['g_team_food'] = json_encode(I('post.teamFood'));//用车人数
        $data['g_cj_info'] = I('post.cjinfo');//差价说明
        $data['g_no_team'] = json_encode(I('post.nteam'));//不成团
        $data['g_wx_info'] = I('post.wxinfo');//温馨提示
        $data['g_user_id'] = $this->userId;
        $result = M('group_info')->add($data);

        if (!$result) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '保存失败'));
        }
    }

    /**
     * 添加或者修改跟团游价格日历
     */
    public function addPriceCa($rl, $code)
    {
        M('group_price')->where(array('g_code' => $code, 'g_user_code' => $this->userId))->delete();
        foreach ($rl as $r) {
            $data['g_go_time'] = $r['priceDate'];//日期
            $data['g_man_my_price'] = $r['menplatformprice'];//成人平台价格
            $data['g_man_mark_price'] = $r['menmarketprice'];//成人市场价
            $data['g_man_js_price'] = $r['mencloseprice'];//成人结算价
            $data['g_is_child'] = $r['ischildtravel']['val'];//是否有儿童价
            $data['g_child_my_price'] = $r['ischildtravel']['platformprice'];//儿童平台价格
            $data['g_child_js_price'] = $r['ischildtravel']['closeprice'];//儿童市场价格
            $data['g_df_ch'] = $r['issingleroom']['val'];//单房差 -1为无
            $data['g_df_plat'] = $r['issingleroom']['platformprice'];//单房差 -1为无
            $data['g_df_ch_close'] = $r['issingleroom']['closeprice'];//单房差 -1为无
            $data['g_no_kc_num'] = $r['noneedkc'];//不需要确认库存
            $data['g_need_kc_num'] = $r['needkc'];//需要确认库存
            $data['g_is_buy'] = $r['islimitNum']['val'];//最大购买量
            $data['g_max_buy_num'] = $r['islimitNum']['max'];//最大购买量
            $data['g_min_buy_num'] = $r['islimitNum']['min'];//最低购买量
            $data['g_code'] = $code;
            $data['g_user_code'] = $this->userId;
            $result = M('group_price')->add($data);
            if (!$result) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '操作失败'));
            }
        }
    }

    /**
     * 获取价格日历
     */
    public function getPriceList()
    {
        $list = M('group_price')->where(array('g_user_code' => $this->userId))->select();
        $this->ajaxReturn($list);
    }

    /**
     * 获得产品列表
     */
    public function getGroupList()
    {
        $page = I('post.page');
        if (empty($page)) {
            $page = 0;
        } else {
            $page--;
        }

        //状态
        if (!empty(I('post.type'))) {
            $where['g_is_pass'] = I('post.type');//产品状态
        }
        if ($_SESSION['type'] == 1) {
            $where['g_user_code'] = $this->userId;
        }

        //出发地
        if (!empty(I('post.go'))) {
            $go = I('post.go');
            $where['g_go_address'] = array('like', "%$go%");
        }
        //目的地
        if (!empty(I('post.end'))) {
            $end = I('post.end');
            $where['g_e_address'] = array('like', "%$end%");
        }
        //产品名称
        if (empty(I('post.name'))) {
            $name = I('post.name');
            $where['g_name'] = array('like', "%$name%");
        }
        $where['g_is_del'] = array('neq', "1");

        $list = M('group')->where($where)->limit($page * 10, 10)->order('g_id desc')->select();
        foreach ($list as &$item) {
            if ($item['g_is_pass'] == 1) {
                $item['button'] = '审核中';
                $item['type'] = "审核中请等待";
            } elseif ($item['g_is_pass'] == 2) {
                $item['button'] = '上线';
                $item['type'] = '审核通过可以上线';
            } elseif ($item['g_is_pass'] == 3) {
                $item['button'] = '审核失败';
                $item['type'] = $item['g_is_pass_log'];
            } elseif ($item['g_is_pass'] == 4) {
                $item['button'] = '提交审核';
                $item['type'] = '产品可以提交审核';
            } elseif ($item['g_is_pass'] == 5) {
                $item['button'] = '下线';
                $item['type'] = '上线售卖中';
            } elseif ($item['g_is_pass'] == 6) {
                $item['button'] = '产品已下线';
                $item['type'] = '产品重新编辑提交审核';
            }
        }
        $count = M('group')->where($where)->count();
        $return['info'] = $list;
        $return['page'] = $count;
        $this->ajaxReturn($return);
    }


    /**
     * 获取单个产品详细信息
     */
    public function getGroupInfoByCode()
    {
        $code = I('post.code');
        if ($_SESSION['type'] == 1) {
            $groupBase = M('group')->where(array('g_code' => $code, 'g_user_code' => $this->userId))->find();
            $contact = M('contact')->where(array('c_user_id' => $this->userId, 'c_id' => $groupBase['g_contact_id']))->find();
        }
        if ($_SESSION['type'] == 2) {
            $groupBase = M('group')->where(array('g_code' => $code))->find();
            $contact = M('contact')->where(array('c_id' => $groupBase['g_contact_id']))->find();
        }


        $groupBase['c_name'] = $contact['c_name'];
        $groupBase['g_file'] = json_decode($groupBase['g_file'], true);
        $groupBase['g_service'] = json_decode($groupBase['g_service'], true);
        $groupBase['g_service_phone'] = json_decode($groupBase['g_service_phone'], true);
        $groupBase['g_play_spot'] = json_decode($groupBase['g_play_spot'], true);
        foreach ($groupBase['g_play_spot'] as &$gs) {
            if ($gs['selected'] === 'false') {
                $gs['selected'] = false;
            } else {
                $gs['selected'] = true;
            }
        }
        foreach ($groupBase['g_service'] as &$gsv) {
            if ($gsv === 'true') {
                $gsv = true;
            } else {
                $gsv = false;
            }
        }
        foreach ($groupBase['g_file'] as &$gfl) {
            $gfl['src'] = C('img_url') . $gfl['src'];
            if ($gfl['headImg'] === 'true') {
                $gfl['headImg'] = true;
            } else {
                $gfl['headImg'] = false;
            }
        }
        $groupBase['g_ladder_refund'] = json_decode($groupBase['g_ladder_refund'], true);
        foreach ($groupBase['g_ladder_refund'] as &$glre) {
            if ($glre['wymoney']['val'] === 'true') {
                $glre['wymoney']['val'] = true;
            } else {
                $glre['wymoney']['val'] = false;
            }
        }
        $groupBase['g_yd_time'] = json_decode($groupBase['g_yd_time'], true);
        $groupInfo = M('group_info')->where(array('g_code' => $groupBase['g_code']))->find();
        $groupInfo['g_venu'] = json_decode($groupInfo['g_venu'], true);
        $groupInfo['g_routing'] = json_decode($groupInfo['g_routing'], true);

        foreach ($groupInfo['g_routing'] as &$grt) {
            foreach ($grt['food'] as &$f) {
                if ($f['bool'] === 'true') {
                    $f['bool'] = true;
                } else {
                    $f['bool'] = false;
                }
            }

            if (empty($grt['group'])) {
                $grt['group'] = [];
            }

            foreach ($grt['routeDetail'] as &$gir) {
                if (empty($gir['spotArr'])) {
                    $gir['spotArr'] = [];
                }
                if (empty($i['freetimeplan'])) {
                    $gir['freetimeplan'] = [];
                }
            }


        }
        $groupInfo['g_all_info'] = json_decode($groupInfo['g_all_info'], true);
        $groupInfo['g_zf_info'] = json_decode($groupInfo['g_zf_info'], true);
        $groupInfo['g_littl_tran'] = json_decode($groupInfo['g_littl_tran'], true);
        foreach ($groupInfo['g_littl_tran'] as &$gltr) {
            if ($gltr === 'true') {
                $gltr = true;
            } else {
                $gltr = false;
            }
        }
        $groupInfo['g_no_tick'] = json_decode($groupInfo['g_no_tick'], true);
        foreach ($groupInfo['g_no_tick'] as &$gntc) {
            if ($gntc === 'true') {
                $gntc = true;
            } else {
                $gntc = false;
            }
        }
        $groupInfo['g_no_bc'] = json_decode($groupInfo['g_no_bc'], true);
        foreach ($groupInfo['g_no_bc'] as &$gnbc) {
            if ($gnbc === 'true') {
                $gnbc = true;
            } else {
                $gnbc = false;
            }
        }
        $groupInfo['g_ts_man'] = json_decode($groupInfo['g_ts_man'], true);
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
        $groupInfo['g_team_food'] = json_decode($groupInfo['g_team_food'], true);
        if ($groupInfo['g_team_food']['val'] === 'true') {
            $groupInfo['g_team_food']['val'] = true;
        } else {
            $groupInfo['g_team_food']['val'] = false;
        }
        $groupInfo['g_no_team'] = json_decode($groupInfo['g_no_team'], true);
        foreach ($groupInfo['g_no_team'] as &$gntm) {
            if ($gntm === 'true') {
                $gntm = true;
            } else {
                $gntm = false;
            }
        }
        $groupInfo['g_dfc'] = json_decode($groupInfo['g_dfc'], true);
        foreach ($groupInfo['g_dfc'] as &$gdfc) {
            if ($gdfc === 'true') {
                $gdfc = true;
            } else {
                $gdfc = false;
            }
        }
        $groupInfo['g_child_all_info'] = json_decode($groupInfo['g_child_all_info'], true);
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
        $groupPrice = M('group_price')->where(array('g_code' => $groupBase['g_code']))->select();
        $sortArray = [];
        foreach ($groupPrice as &$glist) {
            $glist['sort'] = strtotime($glist['g_go_time']);
            $sortArray[] = $glist['sort'];
        }
        array_multisort($sortArray, SORT_ASC, $groupPrice);
        $return['baseInfo'] = $groupBase;
        $return['groupInfo'] = $groupInfo;
        $return['groupPrice'] = $groupPrice;
        $this->ajaxReturn($return);
    }

    /**
     * 上线产品
     */
    public function onGroup()
    {
        $code = I('post.code');
        $pass = M('group')->where(array('g_code' => $code))->getField('g_is_pass');
        if ($pass == '2') {
            $result = M('group')->where(array('g_code' => $code))->save(array('g_is_pass' => 5));
            if (!$result) {
                $this->ajaxReturn(array('code' => '0', '上线失败'));
            }
            $this->ajaxReturn(array('code' => '1', '上线成功'));
        } else {
            $this->ajaxReturn(array('code' => '0', '未审核不准上线'));
        }
    }

    /**
     * 下线产品
     */
    public function downGroup()
    {
        $code = I('post.code');
        $pass = M('group')->where(array('g_code' => $code))->getField('g_is_pass');
        if ($pass == '5') {
            $result = M('group')->where(array('g_code' => $code))->save(array('g_is_pass' => 6));
            if (!$result) {
                $this->ajaxReturn(array('code' => '0', '下线失败'));
            }
            $this->ajaxReturn(array('code' => '1', '下线成功'));
        } else {
            $this->ajaxReturn(array('code' => '0', '下线'));
        }

    }

    /**
     * 提交产品审核
     */
    public function groupToPass()
    {
        $code = I('post.code');
        $pass = M('group')->where(array('g_code' => $code))->getField('g_is_pass');
        if ($pass == '4') {
            $result = M('group')->where(array('g_code' => $code))->save(array('g_is_pass' => 1));
            if (!$result) {
                $this->ajaxReturn(array('code' => '0', '提交失败'));
            }
            $this->ajaxReturn(array('code' => '1', '提交成功'));
        } else {
            $this->ajaxReturn(array('code' => '0', '提交失败'));
        }

    }

    /**
     * 产品删除
     */
    public function delGroup()
    {
        $code = I('post.code');
        $result = M('group')->where(array('g_code' => $code))->save(array('g_is_del' => '1'));
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', '删除失败'));
        } else {
            $this->ajaxReturn(array('code' => '0', '删除成功'));
        }
    }

    /**
     * 获取订单
     */
    public function getOrderList()
    {
        $page = I('post.page');
        $where = [];
        if (empty($page)) {
            $page = 0;
        } else {
            $page--;
        }
        if (!empty(I('post.type'))) {
            $where['g_order_type'] = I('post.type');//订单情况 1 已消费 2 未消费 3 关闭取消订单 4 待付款 5退款中 6 退款成功 7 拒绝退款 8待确认订单
        }
        if (!empty(I('post.orderSn'))) {
            $where['g_order_sn'] = I('post.orderSn');
        }
        if (!empty(I('post.mobile'))) {
            $where['g_mobile'] = I('pos.mobile');
        }
        if (!empty(I('post.name'))) {
            $name = I('post.name');
            $where['g_name'] = array('like', "%$name%");
        }

        if (!empty(I('post.bgTime'))) {
            $where['unix_timestamp(g_go_time)'] = array(array('egt', strtotime(I('post.bgTime') . " 00:00:00")), array('elt', strtotime(I('post.beTime') . " 23:59:59")));
        }


        if (!empty(I('post.cgTime'))) {
            $where['unix_timestamp(g_order_time)'] = array(array('egt', strtotime(I('post.cgTime') . " 00:00:00")), array('elt', strtotime(I('post.ceTime') . " 23:59:59")));
        }

        if ($_SESSION['type'] == 1) {
            $where['g_user_id'] = $this->userId;
        }

        if (empty($where)) {
            $count = M('group_order')->count();
            $orderList = M('group_order')->limit($page * 10, 10)->order('g_order_id desc')->select();
        } else {
            $count = M('group_order')->where($where)->count();
            $orderList = M('group_order')->where($where)->limit($page * 10, 10)->order('g_order_id desc')->select();
        }


        foreach ($orderList as &$o) {
            $o['order_action'] = $this->returnOrderAction()[$o['g_order_type']];

        }
        $return['info'] = $orderList;
        $return['page'] = $count;
        $this->ajaxReturn($return);
    }

    /**
     * 订单按钮返回
     */
    public function returnOrderAction()
    {
        $action = array(
            '1' => array('已消费'),
            '2' => array('确认消费' => 'url'),
            '3' => array('订单取消'),
            '4' => array('待付款'),
            '5' => array('同意退款' => 'url', '拒绝退款' => 'url'),
            '6' => array('已退款'),
            '7' => array('拒绝退款'),
            '8' => array('订单确认' => 'url'),
        );
        return $action;
    }

    /**
     * 确认消费 按钮
     */
    public function confirmConsumption()
    {
        $order_sn = I('post.orderSn');
        $user_id = $this->userId;//供应商Id
        $orderInfo = M('group_order')
            ->field("g_order_type,g_jxs_code,g_order_price,g_man_js_price,g_man_num,g_child_js_price,g_child_num,g_dfc_js_price,g_dfc_num")
            ->where(array("g_order_sn" => $order_sn))->find();

        //订单状态 2未消费
        if(empty($orderInfo) || $orderInfo["g_order_type"] != "2"){
            $this->ajaxReturn(array('code' => '0', 'msg' => '订单错误'));
        }

        //没有经销商的情况 todo 没有经销商 账单清单怎么算
        if(empty($orderInfo["g_jxs_code"])){
            $result = M('group_order')->where(array('g_order_sn' => $order_sn, 'g_user_id' => $user_id))->save(array('g_order_type' => '1', 'g_user_time' => time()));
            if (!$result) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '订单错误'));
            }
            $this->ajaxReturn(array('code' => '1', 'msg' => '操作成功'));
        }

        //经销商不存在的情况
        $jxs_moneyInfo = M('jxs_money')->where(array("jxs_code" => $orderInfo["g_jxs_code"]))->find();
        if(empty($jxs_moneyInfo)){
            $this->ajaxReturn(array('code' => '0', 'msg' => '经销商账户不存在'));
        }

        //经销商佣金 = 订单总价格 - （大人人数 * 结算价格 + 儿童价格 * 儿童结算价格 + 单房差分数 * 单房差结算价格）
        $jxsYJ = $orderInfo["g_order_price"] - ($orderInfo["g_man_js_price"] * $orderInfo["g_man_num"] + $orderInfo["g_child_js_price"] * $orderInfo["g_child_num"] + $orderInfo["g_dfc_js_price"] * $orderInfo["g_dfc_num"]);
//       echo "佣金 = ".$jxsYJ."<br/>";
//       echo "订单总价格 = ".$orderInfo["g_order_price"]."<br/>";
//       echo "大人结算价格 = ".($orderInfo["g_man_js_price"] * $orderInfo["g_man_num"])."<br/>";
//       echo "儿童结算价格 = ".($orderInfo["g_child_js_price"] * $orderInfo["g_child_num"])."<br/>";
//       echo "单房差结算价格 = ".($orderInfo["g_dfc_js_price"] * $orderInfo["g_dfc_num"])."<br/>";

        $jxs_bill_check = M('jxs_bill')->where(array("tb_jxs_code" => $orderInfo["g_jxs_code"],"tb_code" => "1","tb_order_id" => $order_sn))->find();
        //账单表里有记录 错误情况
        if($jxs_bill_check){
            $ModelOne = M();           // 实例化一个空对象
            $ModelOne->startTrans();  // 开启事务
            $omOne = $ModelOne->table('lf_group_order')->where(array('g_order_sn' => $order_sn, 'g_user_id' => $user_id))->save(array('g_order_type' => '1', 'g_user_time' => time()));
            //账单表添加记录
            $saveBillOne["tb_order_id"] = $order_sn;                            //订单编号
            $saveBillOne["tb_jxs_code"] = $orderInfo["g_jxs_code"];            //经销商code
            $saveBillOne["tb_money"] = $jxsYJ;                                  //进账金额
            $saveBillOne["tb_type"] = "group";                                  //订单类型
            $saveBillOne["tb_code"] = "6";                                      //状态 6异常
            $saveBillOne["tb_balance"] = $jxs_moneyInfo["jxs_no_money"];       //账户余额  未加
            $saveBillOne["tb_time"] = date("Y-m-d H:i:s", time());              //时间
            $saveBillOne["tb_remark_info"] = "数据库已有进账数据";              //备注
            $gmOne = $ModelOne->table("lf_jxs_bill")->where(array("tb_jxs_code" => $orderInfo["t_jsx_code"]))->data($saveBillOne)->add();
            if ($omOne && $gmOne) {
                $ModelOne->commit();
                $this->ajaxReturn(array('code' => '1', 'msg' => '操作成功'));
            } else {
                $ModelOne->rollBack();
                $this->ajaxReturn(array('code' => '0', 'msg' => '操作失败，请联系管理员'));
            }
        }
////              var_dump($orderInfo);
//       return;

        $Model = M();           // 实例化一个空对象
        $Model->startTrans();  // 开启事务
        //更新订单状态
        $om = $Model->table('lf_group_order')->where(array('g_order_sn' => $order_sn, 'g_user_id' => $user_id))->save(array('g_order_type' => '1', 'g_user_time' => time()));
        //jxs_money 增加经销商总金额
        $jxs_no_money = $jxs_moneyInfo["jxs_no_money"] + $jxsYJ;                               //未提现金额
        $jxs_all_money = $jxs_moneyInfo["jxs_already_money"] + $jxs_no_money;                 //总金额
        $pm = $Model->table("lf_jxs_money")->where(array("jxs_code" => $orderInfo["g_jxs_code"]))->save(array('jxs_no_money' => $jxs_no_money, 'jxs_all_money' => $jxs_all_money));
        //账单表添加记录
        $saveBill["tb_order_id"] = $order_sn;                            //订单编号
        $saveBill["tb_jxs_code"] = $orderInfo["g_jxs_code"];            //经销商code
        $saveBill["tb_money"] = $jxsYJ;                                   //进账金额  已加
        $saveBill["tb_type"] = "group";                                  //订单类型
        $saveBill["tb_code"] = "1";                                      //状态 1进账
        $saveBill["tb_balance"] = $jxs_no_money;                         //账户余额
        $saveBill["tb_time"] = date("Y-m-d H:i:s", time());             //时间
        $gm = $Model->table("lf_jxs_bill")->where(array("tb_jxs_code" => $orderInfo["g_jxs_code"]))->data($saveBill)->add();
        if ($om && $pm && $gm) {
//           $Model->rollBack();
            $Model->commit();
            $this->ajaxReturn(array('code' => '1', 'msg' => '操作成功'));
        } else {
            $Model->rollBack();
            $this->ajaxReturn(array('code' => '0', 'msg' => '操作失败，请联系管理员'));
        }
    }

    /**
     * 同意退款
     */
    public function agreeRefund()
    {
        $order_sn = I('post.orderSn');
        $user_id = $this->userId;//供应商Id
        $orderType = M('group_order')->where('g_order_sn =' . $order_sn)->getField('g_order_type');
        if ($orderType == '5') {
            $result = M('group_order')->where(array('g_order_sn' => $order_sn, 'g_user_id' => $user_id))->save(array('g_order_type' => '6'));
            if ($result) {
                $this->ajaxReturn(array('code' => '1', 'msg' => '操作成功'));
            } else {
                $this->ajaxReturn(array('code' => '0', 'msg' => '订单错误'));
            }
        } else {
            $this->ajaxReturn(array('code' => '0', 'msg' => '订单错误'));
        }
    }

    /**
     * 拒绝退款
     */
    public function rejectRefund()
    {
        $order_sn = I('post.orderSn');
        $user_id = $this->userId;//供应商Id
        $orderType = M('group_order')->where('g_order_sn =' . $order_sn)->getField('g_order_type');
        if ($orderType == '5') {
            $result = M('group_order')->where(array('g_order_sn' => $order_sn, 'g_user_id' => $user_id))->save(array('g_order_type' => '7'));
            if ($result) {
                $this->ajaxReturn(array('code' => '1', 'msg' => '操作成功'));
            } else {
                $this->ajaxReturn(array('code' => '0', 'msg' => '订单错误'));
            }
        } else {
            $this->ajaxReturn(array('code' => '0', 'msg' => '订单错误'));
        }
    }

    /**
     * 订单确认
     */
    public function confirmOrder()
    {
        $order_sn = I('post.orderSn');
        $user_id = $this->userId;//供应商Id
        $orderType = M('group_order')->where('g_order_sn =' . $order_sn)->getField('g_order_type');
        if ($orderType == '8') {
            $result = M('group_order')->where(array('g_order_sn' => $order_sn, 'g_user_id' => $user_id))->save(array('g_order_type' => '2'));
            if ($result) {
                $this->ajaxReturn(array('code' => '1', 'msg' => '消费成功'));
            } else {
                $this->ajaxReturn(array('code' => '0', 'msg' => '订单错误'));
            }
        } else {
            $this->ajaxReturn(array('code' => '0', 'msg' => '订单错误'));
        }
    }

    /**
     * 上传图片
     */
    public function addr($file, $path, $k)
    {
        header('Content-type:text/html;charset=utf-8');
        $base64_image_content = $file;
        if (preg_match('/^(data:\s*image\/(\w+);base64,)/', $base64_image_content, $result)) {
            $type = $result[2];
            $new_file = $path;
            $new_file = $new_file . time() . $k . ".{$type}";
            file_put_contents($new_file, base64_decode(str_replace($result[1], '', $base64_image_content)));
            return $new_file;
        }

    }

    /**
     * 随机生成产品编号
     */

    function createCode()
    {
        return $this->userId . mt_rand(10, 99) . sprintf('%010d', time() - 946656000);
    }

    /**
     * 开启库存
     */
    public function openkc()
    {
        $date = I('post.date');
        $id = $this->userId;
        $code = I('post.code');
        $where['g_code'] = $code;
        $where['g_user_code'] = $id;
        $where['unix_timestamp(g_go_time)'] = strtotime($date);
        $result = M('group_price')->where($where)->save(array('g_is_open' => 1));
        if ($result) {
            $this->ajaxReturn(array('code' => 1, 'type' => 1));
        }
    }

    /**
     * 关闭库存
     */

    public function closekc()
    {
        $date = I('post.date');
        $id = $this->userId;
        $code = I('post.code');
        $where['g_code'] = $code;
        $where['g_user_code'] = $id;
        $where['unix_timestamp(g_go_time)'] = strtotime($date);
        $result = M('group_price')->where($where)->save(array('g_is_open' => 2));
        if ($result) {
            $this->ajaxReturn(array('code' => 1, 'type' => 2));
        }
    }

    /**
     * 跟团游订单详细信息
     */
    public function getOrderInfo()
    {
        $orderSn = I('post.orderSn');
        if (empty($orderSn)) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '非法操作'));
        }
        $orderInfo = M('group_order')->where(array('g_order_sn' => $orderSn))->find();
        $orderInfo['g_pay_time'] = date('Y-m-d H:i:s', $orderInfo['g_pay_time']);
        $orderInfo['g_zf_info'] = json_decode($orderInfo['g_zf_info'], true);
        $orderInfo['g_identity_info'] = json_decode($orderInfo['g_identity_info'], true);
        $this->ajaxReturn($orderInfo);
    }

    /**
     * 获取账单
     */
    public function getGroupBill()
    {
        $btime = strtotime(I('post.btime') . " 00:00:00");
        $etime = strtotime(I('post.etime') . " 23:59:59");
        if (!empty(I('post.btime'))) {
            $where['g_b_time'] = array('egt', $btime);
            $where['g_e_time'] = array('elt', $etime);
        }
        if ($_SESSION['type'] == 1) {
            $where['g_user_id'] = $this->userId;
        }
        $billList = M('group_bill')->where($where)->select();
        foreach ($billList as &$bl) {
            $bl['g_b_time'] = date('Y-m-d', $bl['g_b_time']);
            $bl['g_e_time'] = date('Y-m-d', $bl['g_e_time']);
        }
        $this->ajaxReturn($billList);
    }

    /**
     * 获取账单中的订单
     */
    public function getBillOrder()
    {
        $beginDate = strtotime(I('post.bdate') . " 00:00:00");
        $endDate = strtotime(I('post.edate') . " 23:59:59");
        $swhere['g_user_time'] = array(array('egt', $beginDate), array('lt', $endDate));
        $swhere['g_order_type'] = 1;//g_man_js_price  g_man_num g_child_num g_child_js_price g_dfc_num g_dfc_js_price
        if ($_SESSION['type'] == 1) {
            $swhere['g_user_id'] = $this->userId;
        }
        if ($_SESSION['type'] == 2) {
            $swhere['g_user_id'] = I('post.uid');
        }

        $groupBill = M('group_order')->where($swhere)->select();
        $this->ajaxReturn($groupBill);
    }
}