<?php
/**
 * Created by zhang.
 * User: zhang
 * Date: 2017/10/19
 * Time: 10:04
 */

namespace Home\Controller;


class TickController extends BaseController
{
    public function newTick()
    {
        $contact = M('contact')->field('c_id,c_name')->where(array('c_user_id' => $this->userId))->select();
        $this->ajaxReturn($contact);
    }

    /**
     * 接受门票产品所有参数
     */
    public function getPostTickInfo()
    {
        $data['t_contract'] = I('post.contract');//合同编号
        $data['t_category'] = I('post.category');//商品类别
        $data['t_tick_city'] = I('post.tickCity');//景点地区
        $data['t_tick_spot'] = I('post.tickSpot');//景点
        $data['t_tick_cat'] = I('post.tickCat');//门票种类
        $data['t_tick_name'] = I('post.tickName');//产品名称
        $data['t_tick_mobile'] = I('post.mobile');//联系人信息
        $data['t_yd_num'] = json_encode(I('post.ynum'));//预定数量
        $data['t_go_b_time'] = json_encode(I('post.enterTime'));//入园开始时间
        $data['t_go_e_time'] = I('post.eTime');//入园结束时间
        $data['t_tick_cost'] = json_encode(I('post.tickCost'));//费用包含
        $data['t_tickService_num'] = json_encode(I('post.tickService'));//客服电话
        $data['t_tick_identity'] = json_encode(I('post.identity'));//身份证限制

        $data['t_another_cost'] = I('post.anothercost');//其他费用
        $data['t_tick_no_contain'] = I('post.costnotcontain');//费用不包含
        $data['t_tick_insurance'] = I('post.insurance');//保险
        $data['t_tick_playerInfo'] = json_encode(I('post.playerInfo'));//游玩人信息
        $data['t_tick_pre_book_time'] = json_encode(I('post.preBookTime'));//提前预定信息
        $dataType = I('post.dataType');
        $tickImg = I('post.img');//图片
        $path = "./Public/tick/";
        foreach ($tickImg as $k => $t) {
//            $size = file_get_contents($t['src']);//
//            if($size >23){
//                $this -> ajaxReturn(array('code' => 0));//这是判定图片大小，后期你们自己开启 base64 格式图片大小会比正常的大1/3.所以设定的时候要比你前端设定的大1/3 ，不然会一直提示错误
//            }
            $ifl = $this->addr($t['src'], $path, $k);
            if (empty($ifl)) {
                $arr = parse_url($t['src']);
                $file[$k]['src'] = $arr['path'];
            } else {
                $file[$k]['src'] = $ifl;
            }
            $file[$k]['head'] = $t['headImg'];
        }
        $data['t_tick_file'] = json_encode($file);
        $data['t_tick_use_address'] = json_encode(I('post.address'));//入园地址
        $data['t_tick_xj_time'] = I('post.xTime');//下架时间
        $data['t_tick_sj_time'] = I('post.sTime');//上架 时间 审核通过自动上架 为1 其他填写时间
 
    return $data;
    }

    /**
     * 添加门票基本信息
     */
    public function addTick()
    {
        $data = $this->getPostTickInfo();
        $data['t_create_time'] = time();
        $data['t_code'] = $this->createCode();
        $data['t_user_id'] = $this->userId;
        $data['t_tick_type'] = 5;
        M('tick')->add($data);
        $rl = I('post.rl');
        $tickType = I('post.dateType');//有效期模式 1 有效期 2 价格日历
        if ($tickType == 1) {
            $flage = $this->addLine($rl, $data['t_code']);
        }
        if ($tickType == 2) {
            $flage = $this->addPriceCa($rl, $data['t_code']);
        }
        if (!$flage) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '添加失败'));
        } else {
            $this->ajaxReturn(array('code' => '1', 'msg' => '添加成功', 'tcode' => $data['t_code']));
        }
    }

    /**
     * 修改门票基本信息
     */
    public function saveTick()
    {
        $data = $this->getPostTickInfo();
        $data['t_tick_type'] = 5;
        $tickId = I('post.code');
        if (empty($tickId)) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '请提交产品编码'));
        }
        M('tick')->where(array('t_code' => $tickId, 't_user_id' => $this->userId))->save($data);
        $rl = I('post.rl');
        $tickType = I('post.dateType');//有效期模式 1 有效期 2 价格日历
        if ($tickType == 1) {
            $flage = $this->addLine($rl, $tickId);
        }
        if ($tickType == 2) {
            $flage = $this->addPriceCa($rl, $tickId);
        }
        if (!$flage) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '更新失败'));
        } else {
            $this->ajaxReturn(array('code' => '1', 'msg' => '更新成功', 't_code' => $tickId));
        }
    }

    /**
     * 获取价格模式
     */
    public function getPriceModel()
    {
        $type = I('dataType');
        $code = I('post.code');
        if ($type == 2) {
            $result = M("tcik_price")->where(array('y_code' => $code))->select();
        } else {
            $result = M('tick_yx')->where(array('p_code' => $code))->select();
        }
        $return['type'] = $type;
        $return['info'] = $result;
        $this->ajaxReturn($result);
    }

    /**
     * 添加/修改门票价格日历
     */
    public function addPriceCa($ri, $code)
    {
        $tickType = 2;
        $riqi = $ri['riqiqishidate'];
        M('tick')->where(array('t_code' => $code, 't_user_id' => $this->userId))->save(array('t_tick_date' => $tickType, 't_tick_ja_ri_yx' => $riqi));
        M("tick_price")->where(array('p_code' => $code, 'p_user_code' => $this->userId))->delete();
        foreach ($ri['priceDateData'] as $i) {
            $data['p_date'] = $i['date'];//时间
            $data['p_mark_price'] = $i['menshiprice'];//市场价
            $data['p_my_price'] = $i['pingtaiprice'];//平台价格
            $data['p_js_price'] = $i['jiesuanprice'];//结算价格
            $data['p_price_rate'] = $i['rate'];//费率
            $data['p_ck'] = $i['kucunStyle'];//库存
            $data['p_code'] = $code;
            $data['p_user_code'] = $this->userId;
            $result = M('tick_price')->add($data);
        }
        if (!$result) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 添加或修改门票的有效期模式
     */
    public function addLine($ri, $code)
    {
        $tickType = 1;//有效期模式 1 有效期 2 价格日历
        $ydata['y_b_time'] = $ri['bdate'];//有效期开始时间
        $ydata['y_e_time'] = $ri['edate'];//有效期结束时间
        $ydata['y_can_use_time'] = json_encode($ri['cdate']);//可用时间c
        $ydata['y_no_user_time'] = json_encode($ri['ndate']);//不可用时间c
        $data['t_tick_mark_price'] = $ri['markPrice'];//市场价
        $data['t_tick_my_price'] = $ri['myPrice'];//平台价格
        $data['t_tick_settle_price'] = $ri['jPrice'];//结算价格
        $data['t_tick_rate'] = $ri['rate'];//费率
        $data['t_tick_kc'] = $ri['kc'];//库存
        $data['t_tick_date'] = $tickType;//费率
        M('tick')->where(array('t_code' => $code, 't_user_id' => $this->userId))->save($data);
        M('tick_y')->where(array('y_code' => $code, 'y_user_code' => $this->userId))->delete();
        $ydata['y_code'] = $code;
        $ydata['y_user_code'] = $this->userId;
        $price = M('tick_y')->add($ydata);

        if (!$price) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 门票产品列表
     */
    public function getTickList()
    {
        $page = I('post.page');
        if (empty($page)) {
            $page = '0';
        } else {
            $page = $page - 1;
        }
        if ($_SESSION['type'] == 1) {
            $conditon['t_user_id'] = $this->userId;
        }

        //上线状态
        if (!empty(I('post.type'))) {
            $conditon['t_tick_type'] = I('post.type');
        }

        //产品名称
        if (!empty(I('post.name'))) {
            $name = I('post.name');
            $conditon['t_tick_name'] = array("like", "%$name%");
        }

        //景点名称
        if (!empty(I('post.spot'))) {
            $spot = I('post.spot');
            $conditon['t_tick_spot'] = array('like', "%$spot%");
        }

        $conditon['t_tick_del'] = array('neq', '1');
        $pageCount = M('tick')->where($conditon)->count();
        $tickList = M('tick')->field('t_tick_spot,t_tick_cat,t_id,t_contract,t_tick_type,t_code,t_category,t_tick_city,t_tick_name,t_tick_sj_time,t_tick_xj_time')->where($conditon)->order('t_create_time desc')->limit($page * 10, 10)->select();
        if ($_SESSION['type'] == 1) {
            foreach ($tickList as &$item) {
                if ($item['t_tick_type'] == 1) {
                    $item['button'] = '审核中';
                    $item['type'] = "审核中请等待";
                } elseif ($item['t_tick_type'] == 2) {
                    $item['button'] = '上线';
                    $item['type'] = '审核通过可以上线';
                } elseif ($item['t_tick_type'] == 3) {
                    $item['button'] = '审核失败';
                    $item['type'] = $item['t_tick_verify_log'];
                } elseif ($item['t_tick_type'] == 4) {
                    $item['button'] = '下线';
                    $item['type'] = '上线售卖中';
                } elseif ($item['t_tick_type'] == 5) {
                    $item['button'] = '提交审核';
                    $item['type'] = '产品可以提交审核';
                } elseif ($item['t_tick_type'] == 6) {
                    $item['button'] = '产品已下线';
                    $item['type'] = '产品重新编辑提交审核';
                }
            }
        }
        if ($_SESSION['type'] == 2) {
            foreach ($tickList as &$item) {
                if ($item['t_tick_type'] == 1) {
                    $item['button'] = '审核通过/审核不通过';
                    $item['type'] = "请审核";
                } elseif ($item['t_tick_type'] == 2) {
                    $item['button'] = '待供应商上线';
                    $item['type'] = '待供应商上线';
                } elseif ($item['t_tick_type'] == 3) {
                    $item['button'] = '审核不通过';
                    $item['type'] = $item['t_tick_verify_log'];
                } elseif ($item['t_tick_type'] == 4) {
                    $item['button'] = '下线';
                    $item['type'] = '正在售卖中';
                } elseif ($item['t_tick_type'] == 5) {
                    $item['button'] = '提交审核';
                    $item['type'] = '产品可以提交审核';
                } elseif ($item['t_tick_type'] == 6) {
                    $item['button'] = '产品已下线';
                    $item['type'] = '供应商已将产品下线';
                }
            }
        }
        $data['page'] = $pageCount;
        $data['tickList'] = $tickList;
        $this->ajaxReturn($data);
    }

    /**
     * 提交审核
     */
    public function toVerify()
    {
        $code = I('post.code');//产品的编号
        $type = M('tick')->field('t_tick_type')->where(array('t_user_id' => $this->userId, 't_code' => $code))->find();
        if (!$type || $type['t_tick_type'] != 5) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $result = M('tick')->where(array('t_user_id' => $this->userId, 't_code' => $code))->save(array('t_tick_type' => 1));
        if ($result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '提交审核失败'));
        } else {
            $this->ajaxReturn(array('code' => '1', 'msg' => '提交审核成功'));
        }
    }

    /**
     * 上线
     */
    public function onTick()
    {
        $code = I('post.code');//产品的编号
        $type = M('tick')->field('t_tick_type')->where(array('t_user_id' => $this->userId, 't_code' => $code))->find();
        if (!$type || $type['t_tick_type'] != 2) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $result = M('tick')->where(array('t_user_id' => $this->userId, 't_code' => $code))->save(array('t_tick_type' => 4));
        if ($result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '提交审核失败'));
        } else {
            $this->ajaxReturn(array('code' => '1', 'msg' => '提交审核成功'));
        }
    }

    /**
     * 下线
     */
    public function downTick()
    {
        $code = I('post.code');//产品的编号
        if ($_SESSION['type'] == 1) {
            $type = M('tick')->field('t_tick_type')->where(array('t_user_id' => $this->userId, 't_code' => $code))->find();
        }
        if ($_SESSION['type'] == 2) {
            $type = M('tick')->field('t_tick_type')->where(array('t_code' => $code))->find();
        }

        if (!$type || $type['t_tick_type'] != 4) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        if ($_SESSION['type'] == 1) {
            $result = M('tick')->where(array('t_user_id' => $this->userId, 't_code' => $code))->save(array('t_tick_type' => 6));
        }
        if ($_SESSION['type'] == 2) {
            $result = M('tick')->where(array('t_code' => $code))->save(array('t_tick_type' => 6));
        }
        if ($result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '下线失败'));
        } else {
            $this->ajaxReturn(array('code' => '1', 'msg' => '下线成功'));
        }
    }

    /**
     * 获取门票信息/编辑接口
     */
    public function getTickInfo()
    {
        $code = I('post.code');
        if (empty($code)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '请提交产品Id'));
        }
        if ($_SESSION['type'] == 1) {
            $tickInfo = M('tick')->where('t_user_id =' . $this->userId . " and t_code = " . $code)->find();

        } else {
            $tickInfo = M('tick')->where("t_code = " . $code)->find();

        }
        $c = M('contact')->field('c_name,c_id')->where(array('c_id' => $tickInfo['t_contract']))->find();
        $tickInfo['c_name'] = $c['c_name'];
        $tickInfo['t_tick_cost'] = json_decode($tickInfo['t_tick_cost'], true);
        $tickInfo['t_tick_file'] = json_decode($tickInfo['t_tick_file'], true);
        foreach ($tickInfo['t_tick_file'] as &$ttf) {
            if ($ttf['head'] === 'false') {
                $ttf['head'] = false;
            } else {
                $ttf['head'] = true;
            }
        }
        $tickInfo['t_tick_use_address'] = json_decode($tickInfo['t_tick_use_address'], true);//入园地址
        $tickInfo['t_tickService_num'] = json_decode($tickInfo['t_tickService_num'], true);//客服电话
        $tickInfo['t_go_b_time'] = json_decode($tickInfo['t_go_b_time'], true);//客服电话
        $tickInfo['t_tick_pre_book_time'] = json_decode($tickInfo['t_tick_pre_book_time'], true);//客服电话
        $tickInfo['t_tick_identity'] = json_decode($tickInfo['t_tick_identity'], true);//客服电话
        if ($tickInfo['t_tick_identity']['errTip'] === 'false') {
            $tickInfo['t_tick_identity']['errTip'] = false;
        } else {
            $tickInfo['t_tick_identity']['errTip'] = true;
        }
        foreach ($tickInfo['t_go_b_time'] as &$tgbt) {
            if ($tgbt['bcInfo']['bool'] === 'false') {
                $tgbt['bcInfo']['bool'] = false;
            } else {
                $tgbt['bcInfo']['bool'] = true;
            }
        }
        $tickInfo['t_tick_playerInfo'] = json_decode($tickInfo['t_tick_playerInfo'], true);//游玩人信息
        if ($tickInfo['t_tick_playerInfo']['num']['disabled'] === 'false') {
            $tickInfo['t_tick_playerInfo']['num']['disabled'] = false;
        } else {
            $tickInfo['t_tick_playerInfo']['num']['disabled'] = true;
        }

        foreach ($tickInfo['t_tick_playerInfo']['identityInfo'] as &$idif) {
            if ($idif === 'false') {
                $idif = false;
            } else {
                $idif = true;
            }
        }

        foreach ($tickInfo['t_tick_playerInfo']['papersInfo'] as &$ppif) {
            if ($ppif === 'false') {
                $ppif = false;
            } else {
                $ppif = true;
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
//        $tickInfo['t_tick_pre_book_time'] = json_decode($tickInfo['t_tick_pre_book_time'], true);//提前预定信息

        foreach ($tickInfo['t_tick_file'] as &$item) {
            $item['src'] = C('img_url') . $item['src'];
            $item['headImg'] = $item['head'];
        }
        $tickInfo['t_yd_num'] = json_decode($tickInfo['t_yd_num'], true);
        if ($tickInfo['t_yd_num']['errTip'] === 'false') {
            $tickInfo['t_yd_num']['errTip'] = false;
        } else {
            $tickInfo['t_yd_num']['errTip'] = true;
        }
        $tickInfo['enter_time'] = $tickInfo['t_go_b_time'];
        if ($tickInfo['t_tick_date'] == 1) {
            if ($_SESSION['type'] == 1) {
                $result = M('tick_y')->where(array('y_code' => $code, 'y_user_code' => $this->userId))->find();

            } else {
                $result = M('tick_y')->where(array('y_code' => $code))->find();
            }

            $result['y_can_use_time'] = json_decode($result['y_can_use_time'], true);
            foreach ($result['y_can_use_time'] as &$ycut) {
                if ($ycut === 'false') {
                    $ycut = false;
                } else {
                    $ycut = true;
                }
            }
            $result['y_no_user_time'] = json_decode($result['y_no_user_time'], true);
        } elseif ($tickInfo['t_tick_date'] == 2) {

            if ($_SESSION['type'] == 1) {
                $result = M('tick_price')->where(array('p_code' => $code, 'p_user_code' => $this->userId))->select();

            } else {
                $result = M('tick_price')->where(array('p_code' => $code))->select();

            }


        }
        if (empty($tickInfo)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '不存在该产品'));
        }

        $return['tickInfo'] = $tickInfo;
        $return['rl'] = $result;

        $this->ajaxReturn($return);
    }

    /**
     * 删除产品
     */
    public function delTick()
    {
        $id = I('post.code');
        if (empty($id)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '请提交产品id'));
        }
        $result = M('tick')->where('t_user_id =' . $this->userId . " and t_code= " . $id)->save(array('t_tick_del' => '1'));
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '删除失败'));
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '删除成功'));
    }

    /**
     * 查看订单
     */
    public function getOrderList()
    {
        $page = I('post.page');
        $condition = [];
        if (empty($page)) {
            $page = 0;
        } else {
            $page--;
        }
        if ($_SESSION['type'] == 1) {
            $condition['t_tick_id'] = $this->userId;
        }

        if (!empty(I('post.code'))) {
            $condition['t_tick_code'] = I('post.code');
        }
        if (!empty(I('post.mobile'))) {
            $condition['t_order_user_mobile'] = I('post.mobile');
        }
        if (!empty(I('post.orderType'))) {
            $condition['t_tick_order_type'] = I('post.orderType');
        }

        if (!empty(I('post.buseTime'))) {
            $condition['t_tick_use_time'] = array(array('egt', strtotime(I('post.buseTime') . " 00:00:00")), array('elt', strtotime(I('post.euseTime') . " 23:59:59")));
        }


        if (!empty(I('post.baddTime'))) {
            $condition['unix_timestamp(t_tick_create_time)'] = array(array('egt', strtotime(I('post.baddTime') . " 00:00:00")), array('elt', strtotime(I('post.eaddTime') . " 23:59:59")));
        }

        if (empty($condition)) {
            $pageCount = M('tick_order')->count();
            $orderList = M('tick_order')->limit($page * 10, 10)->select();
        } else {
            $pageCount = M('tick_order')->where($condition)->count();
            $orderList = M('tick_order')->where($condition)->limit($page * 10, 10)->select();
        }

        foreach ($orderList as &$o) {
            $o['order_action'] = $this->returnOrderAction()[$o['t_tick_order_type']];
            $o['t_play_info'] = json_decode($o['t_play_info'], true);
            if (!empty($o['t_tick_use_time'])) {
                $o['t_tick_use_time'] = date('Y-m-d H:i:s', $o['t_tick_use_time']);
            }

        }
        $data['page'] = $pageCount;
        $data['orderList'] = $orderList;
        $this->ajaxReturn($data);
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
     * 2017/12/21 修改 刘
     */
    public function confirmConsumption()
    {
        $order_sn = I('post.orderSn');
        if(empty($order_sn)){
            return;
        }
        $user_id = $this->userId;//供应商Id
        $orderInfo = M('tick_order')->field("t_tick_order_type,t_jsx_code,t_order_sn,t_tick_js_price")->where(array("t_order_sn" => $order_sn))->find();
        //订单状态 2未消费
        if(empty($orderInfo) || $orderInfo["t_tick_order_type"] != "2"){
            $this->ajaxReturn(array('code' => '0', 'msg' => '订单错误'));
        }

        //没有经销商的情况 todo 没有经销商 账单清单怎么算
        if(empty($orderInfo["t_jsx_code"])){
            $result = M('tick_order')->where(array('t_order_sn' => $order_sn, 't_tick_id' => $user_id))->save(array('t_tick_order_type' => '1', 't_tick_use_time' => time()));
            if (!$result) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '订单错误'));
            }
            $this->ajaxReturn(array('code' => '1', 'msg' => '操作成功'));
        }

        //经销商存在的情况
        $jxs_moneyInfo = M('jxs_money')->where(array("jxs_code" => $orderInfo["t_jsx_code"]))->find();
        if(empty($jxs_moneyInfo)){
            $this->ajaxReturn(array('code' => '0', 'msg' => '经销商账户不存在'));
        }

        $jxs_bill_check = M('jxs_bill')->where(array("tb_jxs_code" => $orderInfo["t_jsx_code"],"tb_code" => "1","tb_order_id" => $order_sn))->find();
        //账单表里有记录 错误情况
        if($jxs_bill_check){
            $ModelOne = M();           // 实例化一个空对象
            $ModelOne->startTrans();  // 开启事务
            $omOne = $ModelOne->table('lf_tick_order')->where(array('t_order_sn' => $order_sn, 't_tick_id' => $user_id))->save(array('t_tick_order_type' => '1', 't_tick_use_time' => time()));
            //账单表添加记录
            $saveBillOne["tb_order_id"] = $order_sn;                            //订单编号
            $saveBillOne["tb_jxs_code"] = $orderInfo["t_jsx_code"];            //经销商code
            $saveBillOne["tb_money"] = $orderInfo["t_tick_js_price"];          //进账金额
            $saveBillOne["tb_type"] = "tick";                                   //订单类型
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

        $Model = M();           // 实例化一个空对象
        $Model->startTrans();  // 开启事务
        //更新订单状态
        $om = $Model->table('lf_tick_order')->where(array('t_order_sn' => $order_sn, 't_tick_id' => $user_id))->save(array('t_tick_order_type' => '1', 't_tick_use_time' => time()));
        //jxs_money 增加经销商总金额
        $jxs_no_money = $jxs_moneyInfo["jxs_no_money"] + $orderInfo["t_tick_js_price"];      //未提现金额
        $jxs_all_money = $jxs_moneyInfo["jxs_already_money"] + $jxs_no_money;                 //总金额
        $pm = $Model->table("lf_jxs_money")->where(array("jxs_code" => $orderInfo["t_jsx_code"]))->save(array('jxs_no_money' => $jxs_no_money, 'jxs_all_money' => $jxs_all_money));
        //账单表添加记录
        $saveBill["tb_order_id"] = $order_sn;                            //订单编号
        $saveBill["tb_jxs_code"] = $orderInfo["t_jsx_code"];            //经销商code
        $saveBill["tb_money"] = $orderInfo["t_tick_js_price"];          //进账金额  已加
        $saveBill["tb_type"] = "tick";                                   //订单类型
        $saveBill["tb_code"] = "1";                                      //状态 1进账
        $saveBill["tb_balance"] = $jxs_no_money;                         //账户余额
        $saveBill["tb_time"] = date("Y-m-d H:i:s", time());             //时间
        $gm = $Model->table("lf_jxs_bill")->where(array("tb_jxs_code" => $orderInfo["t_jsx_code"]))->data($saveBill)->add();
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
        $orderType = M('tick_order')->where('t_order_sn =' . $order_sn)->getField('t_tick_order_type');
        if ($orderType == '5') {
            $result = M('tick_order')->where(array('t_order_sn' => $order_sn, 't_tick_id' => $user_id))->save(array('t_tick_order_type' => '6'));
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
        $orderType = M('tick_order')->where('t_order_sn =' . $order_sn)->getField('t_tick_order_type');
        if ($orderType == '5') {
            $result = M('tick_order')->where(array('t_order_sn' => $order_sn, 't_tick_id' => $user_id))->save(array('t_tick_order_type' => '7'));
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
        $orderType = M('tick_order')->where('t_order_sn =' . $order_sn)->getField('t_tick_order_type');
        if ($orderType == '8') {
            $result = M('tick_order')->where(array('t_order_sn' => $order_sn, 't_tick_id' => $user_id))->save(array('t_tick_order_type' => '2'));
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
     * 开启库存
     */
    public function openkc()
    {
        $date = I('post.date');
        $id = $this->userId;
        $code = I('post.code');
        $where['p_code'] = $code;
        $where['p_user_code'] = $id;
        $where['unix_timestamp(p_date)'] = strtotime($date);
        $result = M('tick_price')->where($where)->save(array('p_is_open' => 1));
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
        $where['p_code'] = $code;
        $where['p_user_code'] = $id;
        $where['unix_timestamp(p_date)'] = strtotime($date);
        $result = M('tick_price')->where($where)->save(array('p_is_open' => 2));
        if ($result) {
            $this->ajaxReturn(array('code' => 1, 'type' => 1));
        }
    }

    /**
     * 获取订单详情
     */
    public function getOrderInfo()
    {
        $orderSn = I('post.orderSn');
        $orderInfo = M('tick_order')->where(array('t_order_sn' => $orderSn))->find();
        $orderInfo['t_play_info'] = json_decode($orderInfo['t_play_info'], true);
        $orderInfo['t_refund_time'] = strtotime("Y-,-d H:i:s", $orderInfo['t_refund_time']);
        $this->ajaxReturn($orderInfo);
    }

    /**
     * 获取账单
     */
    public function getTickBill()
    {

        $btime = strtotime(I('post.btime') . " 00:00:00");
        $etime = strtotime(I('post.etime') . " 23:59:59");
        if (!empty(I('post.btime'))) {
            $where['tb_b_time'] = array('egt', $btime);
            $where['tb_e_time'] = array('elt', $etime);
        }
        if ($_SESSION['type'] == 1) {
            $where['tb_user_id'] = $this->userId;
        }


        $billList = M('tick_bill')->where($where)->select();
        foreach ($billList as &$bl) {
            $bl['tb_b_time'] = date('Y-m-d', $bl['tb_b_time']);
            $bl['tb_e_time'] = date('Y-m-d', $bl['tb_e_time']);
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
        $where['t_tick_use_time'] = array(array('egt', $beginDate), array('lt', $endDate));
        $where['t_tick_order_type'] = 1;
        if ($_SESSION['type'] == 1) {
            $swhere['t_tick_id'] = $this->userId;
        }

        if ($_SESSION['type'] == 2) {
            $where['t_tick_id'] = I('post.uid');
        }

        $groupBill = M('tick_order')->where($where)->select();
        foreach ($groupBill as &$o) {
            $o['order_action'] = $this->returnOrderAction()[$o['t_tick_order_type']];
            $o['t_play_info'] = json_decode($o['t_play_info'], true);
            if (!empty($o['t_tick_use_time'])) {
                $o['t_tick_use_time'] = date('Y-m-d H:i:s', $o['t_tick_use_time']);
            }
        }
        $this->ajaxReturn($groupBill);
    }
}
