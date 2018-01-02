<?php
/**
 * Created by zhang.
 * User: zhang
 * Date: 2017/10/19
 * Time: 15:37
 */

namespace Home\Controller;


class SceneryController extends BaseController
{

    public function newSceney()
    {
        $contact = M('contact')->where('c_user_id=' . $this->userId)->select();
        $this->ajaxReturn($contact);
    }

    /**
     * 添加景点
     */
    public function addView()
    {
        $vdata['v_city'] = I('post.city');//景点城市
        $vdata['v_name'] = I('post.name');//景点名称
        $vdata['v_address'] = I('post.address');//景点地址
        $vdata['v_des'] = I('post.des');//景点介绍
        $vdata['v_user_id'] = $this->userId;//商户id
        $vdata['v_time'] = time();
        $vdata['v_code'] = $this->createCode();
        $img = I('post.img');
        $path = "./Public/hotel/view/";
        $bifile = array();
        foreach ($img as $k => $t) {
            foreach ($t['srcArr'] as $m => $bi) {
                $bifile[$m]['src'] = $this->addr($bi['src'], $path, $k . $m);
                $bifile[$m]['imgtitle'] = $bi['imgtitle'];
            }
            $file[$k]['srcArr'] = $bifile;
            $file[$k]['des'] = $t['imgdescribe'];
            $file[$k]['addImgshow'] = $t['addImgshow'];
            $bifile = array();
        }
        $vdata['v_img'] = json_encode($file);
        $result = M('view')->add($vdata);
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '添加失败'));
        }
        $tickInfo = I('post.tickInfo');
        foreach ($tickInfo as $t) {
            $tdata['v_tick_des'] = $t['des'];//门票简介
            $tdata['v_tick_men'] = $t['menNum'];//成人数量
            $tdata['v_tick_c'] = $t['cNum'];//儿童数量
            $tdata['v_tick_t_b'] = $t['enterWay'];//入园第一步骤
            $tdata['v_tick_t_e'] = $t['enterWay2'];//入园第二步骤
            $tdata['v_tick_is'] = $t['isUse'];//是否选择时间1 是 0 否
            $tdata['v_tick_play_time_b'] = $t['userBeginTime'];//游玩开始时间
            $tdata['v_tick_play_time_e'] = $t['userEndTime'];//游玩结束时间
            $tdata['v_tick_info'] = $t['otherInfo'];//其他说明
            $tdata['v_user_id'] = $this->userId;
            $tdata['v_tick_code'] = $vdata['v_code'];//景点编码
            $tdata['v_tick_is_show'] = $vdata['bookTimeShow'];//景点编码
            $tdata['v_tick_down_show'] = $vdata['downmenulist'];//景点编码
            $tdata['v_time'] = time();
            $result = M('view_tick')->add($tdata);
            if (!$result) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '添加失败'));
            }
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '添加成功'));
    }


    /**
     * 获得所有景点
     */
    Public function getAllView()
    {
        $page = I('post.page');
        if (empty($page)) {
            $page = 0;
        } else {
            $page--;
        }
        if ($_SESSION['type'] == 1) {
            $count = M('view')->where('v_user_id=' . $this->userId)->count();
            $result = M('view')->where('v_user_id=' . $this->userId)->limit($page * 10, 10)->order('v_id desc')->select();
        }
        if ($_SESSION['type'] == 2) {
            $count = M('view')->count();
            $result = M('view')->limit($page * 10, 10)->order('v_id desc')->select();
        }
        $return['page'] = $count;
        $return['info'] = $result;
        $this->ajaxReturn($return);
    }

    /**
     * 获得单个景点信息
     */
    public function getOnlyView()
    {
        $code = I('post.code');
        $viewInfo = M('view')->where('v_code=' . $code)->find();
        $viewInfo['v_img'] = json_decode($viewInfo['v_img'], true);
        $tickInfo = M('view_tick')->where('v_tick_code=' . $code)->select();
        $viewInfo['tickInfo'] = $tickInfo;
        foreach ($viewInfo['v_img'] as &$g) {
            foreach ($g['srcArr'] as &$sai) {
                $sai['src'] = c('img_url') . $sai['src'];
            }
            if ($g['addImgshow'] === 'false') {
                $g['addImgshow'] = false;
            } else {
                $g['addImgshow'] = true;
            }
            $g['imgdescribe'] = $g['des'];
        }
        $this->ajaxReturn($viewInfo);
    }

    /**
     * 更新景点信息
     */
    public function saveViewInfo()
    {
        $code = I('post.code');
        if (empty($code)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法请求！'));
        }
        $vdata['v_city'] = I('post.city');//景点城市
        $vdata['v_name'] = I('post.name');//景点名称
        $vdata['v_address'] = I('post.address');//景点地址
        $vdata['v_des'] = I('post.des');//景点介绍
        $img = I('post.img');
        $path = "./Public/hotel/view/";
        $bifile = array();
        foreach ($img as $k => $t) {
            foreach ($t['srcArr'] as $m => $bi) {
                $bifile[$m]['src'] = $this->addr($bi['src'], $path, $k . $m);

                if (empty($bifile[$m]['src'])) {
                    $arr = parse_url($bi['src']);
                    $bifile[$m]['src'] = $arr['path'];
                }
                $bifile[$m]['imgtitle'] = $bi['imgtitle'];
            }
            $file[$k]['srcArr'] = $bifile;
            $file[$k]['des'] = $t['imgdescribe'];
            $file[$k]['addImgshow'] = $t['addImgshow'];
            $bifile = array();
        }
        $vdata['v_img'] = json_encode($file);
        $result = M('view')->where('v_user_id=' . $this->userId . " and v_code = " . $code)->save($vdata);
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '添加失败'));
        }
        $tickInfo = I('post.tickInfo');
        M('view_tick')->where('v_user_id =' . $this->userId . " and v_tick_code=" . $code)->delete();
        foreach ($tickInfo as $t) {
            $tdata['v_tick_des'] = $t['des'];//门票简介
            $tdata['v_tick_men'] = $t['menNum'];//成人数量
            $tdata['v_tick_c'] = $t['cNum'];//儿童数量
            $tdata['v_tick_t_b'] = $t['enterWay'];//入园第一步骤
            $tdata['v_tick_t_e'] = $t['enterWay2'];//入园第二步骤
            $tdata['v_tick_is'] = $t['isUse'];//是否选择时间1 是 0 否
            $tdata['v_tick_play_time_b'] = $t['userBeginTime'];//游玩开始时间
            $tdata['v_tick_play_time_e'] = $t['userEndTime'];//游玩结束时间
            $tdata['v_tick_info'] = $t['otherInfo'];//其他说明
            $tdata['v_user_id'] = $this->userId;
            $tdata['v_tick_code'] = $code;//景点编码
            $tdata['v_time'] = time();
            $result = M('view_tick')->add($tdata);
            if (!$result) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '添加失败'));
            }
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '添加成功'));

    }


    /**
     * 删除景点
     */
    public function delView()
    {
        $code = I('post.code');
        $viewInfo = M('view')->where('v_code=' . $code)->find();
        if (!$viewInfo) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '不存在该景点'));
        }
        $result1 = M('view')->where('v_code=' . $code . " and v_user_id=" . $this->userId)->delete();
        if (!$result1) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '删除失败'));
        }
        $result2 = M('view_tick')->where('v_tick_code=' . $code . " and v_user_id = " . $this->userId);
        if (!$result2) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '删除失败'));
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '删除成功'));
    }

    /**
     * 餐饮数据添加
     */
    public function addFood()
    {
        $fdata['f_city'] = I('post.city');//餐厅城市
        $fdata['f_name'] = I('post.name');//餐厅名称
        $fdata['f_address'] = I('post.address');//餐厅地址
        $fdata['f_code'] = $this->createCode();
        $fdata['f_time'] = time();
        $fdata['f_user_id'] = $this->userId;
        $img = I('post.img');
        $path = "./Public/hotel/food/";

        $bifile = array();
        foreach ($img as $k => $t) {
            foreach ($t['srcArr'] as $m => $bi) {
                $bifile[$m]['src'] = $this->addr($bi['src'], $path, $k . $m);
                $bifile[$m]['imgtitle'] = $bi['imgtitle'];
            }
            $file[$k]['srcArr'] = $bifile;
            $file[$k]['des'] = $t['imgdescribe'];
            $file[$k]['addImgshow'] = $t['addImgshow'];
            $bifile = array();
        }


        $fdata['f_img'] = json_encode($file);
        $result = M('food')->add($fdata);
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '添加成功'));
        }
        $foodDes = I('post.fdes');
        foreach ($foodDes as $f) {
            $sdata['f_name'] = $f['name'];//餐饮名称
            $sdata['f_num'] = $f['num'];//使用人数
            $sdata['f_des'] = $f['des'];//餐饮说明
            $dinetime = $f['dinetime'];
            $sdata['f_b_time'] = $dinetime['fromH'] . ":" . $dinetime['fromM'];//就餐开始时间
            $sdata['f_e_time'] = $dinetime['toH'] . ":" . $dinetime['toM'];;//就餐结束时间
            $sdata['f_b_way'] = $f['dineway'];//就餐方式步骤一
            $sdata['f_e_way'] = $f['dineway2'];//就餐方式步骤er
            $sdata['f_use_is'] = $f['isUse'];//就餐时间是否需要预定
            $sdata['f_use_b_time'] = $f['userBeginTime'];//开始时间
            $sdata['f_use_e_time'] = $f['userEndTime'];//开始时间
            $sdata['f_code'] = $fdata['f_code'];//餐厅编码
            $sdata['f_user_id'] = $this->userId;//用户id
            $sdata['f_info'] = $f['info'];//其他说明
            $result = M('food_use')->add($sdata);
            if (!$result) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '添加失败'));
            }
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '添加成功'));

    }

    /**
     * 餐饮数据更新
     */
    public function saveFood()
    {
        $fdata['f_city'] = I('post.city');//餐厅城市
        $fdata['f_name'] = I('post.name');//餐厅名称
        $fdata['f_address'] = I('post.address');//餐厅地址
        $code = I('post.code');
        $fdata['f_time'] = time();
        $img = I('post.img');
        $path = "./Public/hotel/food/";
        $bifile = array();
        foreach ($img as $k => $t) {
            foreach ($t['srcArr'] as $m => $bi) {
                $bifile[$m]['src'] = $this->addr($bi['src'], $path, $k . $m);
                if (empty($bifile[$m]['src'])) {
                    $arr = parse_url($bi['src']);
                    $bifile[$m]['src'] = $arr['path'];
                }
                $bifile[$m]['imgtitle'] = $bi['imgtitle'];
            }
            $file[$k]['srcArr'] = $bifile;
            $file[$k]['des'] = $t['imgdescribe'];
            $file[$k]['addImgshow'] = $t['addImgshow'];
            $bifile = array();
        }

        $fdata['f_img'] = json_encode($file);
        $result = M('food')->where(array('f_user_id' => $this->userId, 'f_code' => $code))->save($fdata);
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '添加失败'));
        }
        M('food_use')->where(array('f_code' => $code))->delete();
        $foodDes = I('post.fdes');
        foreach ($foodDes as $f) {
            $sdata['f_name'] = $f['name'];//餐饮名称
            $sdata['f_num'] = $f['num'];//使用人数
            $sdata['f_des'] = $f['des'];//餐饮说明
            $dinetime = $f['dinetime'];
            $sdata['f_b_time'] = $dinetime['fromH'] . ":" . $dinetime['fromM'];//就餐开始时间
            $sdata['f_e_time'] = $dinetime['toH'] . ":" . $dinetime['toM'];;//就餐结束时间
            $sdata['f_b_way'] = $f['dineway'];//就餐方式步骤一
            $sdata['f_e_way'] = $f['dineway2'];//就餐方式步骤二
            $sdata['f_use_is'] = $f['isUse'];//就餐时间是否需要预定
            $sdata['f_use_b_time'] = $f['userBeginTime'];//开始时间
            $sdata['f_use_e_time'] = $f['userEndTime'];//开始时间
            $sdata['f_code'] = $code;//餐厅编码
            $sdata['f_user_id'] = $this->userId;//l用户id
            $sdata['f_info'] = $f['info'];//其他说明
            $result = M('food_use')->add($sdata);
            if (!$result) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '添加失败'));
            }
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '添加成功'));
    }

    /**
     * 餐饮数据列表
     */

    public function getFoodList()
    {
        $page = I('post.page');
        if (empty($page)) {
            $page = 0;
        } else {
            $page--;
        }
        if ($_SESSION['type'] == 1) {
            $foodInfo = M('food')->where('f_user_id=' . $this->userId)->limit($page * 10, 10)->order('f_id desc')->select();
            $count = M('food')->where('f_user_id=' . $this->userId)->count();
        }
        if ($_SESSION['type'] == 2) {
            $foodInfo = M('food')->limit($page * 10, 10)->order('f_id desc')->select();
            $count = M('food')->count();
        }

        $return['page'] = $count;
        $return['info'] = $foodInfo;
        $this->ajaxReturn($return);
    }

    /**
     *获取餐饮单条数据
     */
    public function getOnlyFood()
    {
        $code = I('post.code');
        if ($_SESSION['type'] == 1) {
            $foodInfo = M('food')->where('f_user_id=' . $this->userId . " and f_code=" . $code)->find();
            $foodUseInfo = M('food_use')->where('f_user_id=' . $this->userId . " and f_code=" . $code)->select();
        }
        if ($_SESSION['type'] == 2) {
            $foodInfo = M('food')->where(" f_code=" . $code)->find();
            $foodUseInfo = M('food_use')->where("f_code=" . $code)->select();
        }

        $foodInfo['f_img'] = json_decode($foodInfo['f_img'], true);
        foreach ($foodInfo['f_img'] as &$fimg) {
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
        $foodInfo['useInfo'] = $foodUseInfo;
        $this->ajaxReturn($foodInfo);
    }

    /**
     *删除餐数据
     */
    public function delFood()
    {
        $code = I('post.code');
        $foodInfo = M('food')->where('f_user_id=' . $this->userId . " and f_code=" . $code)->find();
        if (!$foodInfo) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '没有该条数据'));
        }
        $result1 = M('food')->where('f_user_id=' . $this->userId . " and f_code=" . $code)->delete();
        if (!$result1) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '删除失败'));
        }
        $result2 = M('food_use')->where('f_user_id=' . $this->userId . " and f_code=" . $code)->delete();
        if (!$result2) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '删除失败'));
        }

        $this->ajaxReturn(array('code' => '1', 'msg' => '删除成功'));
    }

    /**
     * 添加酒店
     */

    public function addHotel()
    {

        $hdata['h_other_info'] = I('post.otherInfo');//酒店其他说明

        $hdata['h_code'] = $this->createCode();
        $hdata['h_city'] = I('post.city');//酒店地址
        $hdata['h_name'] = I('post.name');//酒店名称
        $hdata['h_address'] = I('post.address');//酒店具体地址
        $hdata['h_check_time'] = I('post.checkTime');//入住时间
        $hdata['h_out_time'] = I('post.outTime');//退房时间
        $hdata['h_prove'] = I('post.prove');//1姓名 2 身份证 3手机号 4 实体凭证 5 其他
        $hdata['h_introduction'] = I('post.introduction');//酒店简介
        $hdata['h_des'] = I('post.des');//酒店描述
        $hdata['h_user_id'] = $this->userId;
        $tickImg = I('post.hoteldetail');
        $path = "./Public/hotel/hotel/";
        $bifile = array();
        foreach ($tickImg as $k => $t) {
            foreach ($t['srcArr'] as $m => $bi) {
                $bifile[$m]['src'] = $this->addr($bi['src'], $path, $k . $m);
                $bifile[$m]['imgtitle'] = $bi['imgtitle'];
            }
            $file[$k]['srcArr'] = $bifile;
            $file[$k]['des'] = $t['imgdescribe'];
            $file[$k]['addImgshow'] = $t['addImgshow'];
            $bifile = array();
        }
        $hdata['h_img'] = json_encode($file);
        $result = M('hotel')->add($hdata);
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '添加失败'));
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '添加成功'));
    }

    /**
     * 获得酒店列表
     */
    public function getHotelList()
    {
        $page = I('post.page');
        if (empty($page)) {
            $page = 0;
        } else {
            $page--;
        }
        if ($_SESSION['type'] == 1) {
            $result = M('hotel')->field('h_id,h_code,h_city,h_name,h_address')->limit($page * 10, 10)->order('h_id desc')->select();
            $count = M('hotel')->count();
        }
        if ($_SESSION['type'] == 2) {
            $result = M('hotel')->field('h_id,h_code,h_city,h_name,h_address')->limit($page * 10, 10)->order('h_id desc')->select();
            $count = M('hotel')->count();
        }

        $return['page'] = $count;
        $return['info'] = $result;
        $this->ajaxReturn($return);
    }

    /**
     * 查看酒店信息
     */
    public function getHotelInfo()
    {
        $code = I('post.code');
        if ($_SESSION['type'] == 1) {
            $info = M('hotel')->where('h_user_id = ' . $this->userId . " and h_code=" . $code)->find();
        }

        if ($_SESSION['type'] == 2) {
            $info = M('hotel')->where(" h_code=" . $code)->find();
        }

        if (empty($info)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '不存在该酒店'));
        }
        $info['h_img'] = json_decode($info['h_img'], true);
        foreach ($info['h_img'] as &$him) {
            foreach ($him['srcArr'] as &$srd) {
                $srd['src'] = C('img_url') . $srd['src'];
            }
            if ($him['addImgshow'] === 'false') {
                $him['addImgshow'] = false;
            } else {
                $him['addImgshow'] = true;
            }
            $him['imgdescribe'] = $him['des'];
        }
        $this->ajaxReturn($info);
    }

    /**
     * 更新酒店信息
     */
    public function saveHotelInfo()
    {
        $code = I('post.code');
        $hdata['h_city'] = I('post.city');//酒店地址
        $hdata['h_name'] = I('name');//酒店名称
        $hdata['h_address'] = I('address');//酒店具体地址
        $hdata['h_check_time'] = I('checkTime');//入住时间
        $hdata['h_out_time'] = I('outTime');//退房时间
        $hdata['h_prove'] = I('prove');//1姓名 2 身份证 3手机号 4 实体凭证 5 其他
        $hdata['h_introduction'] = I('introduction');//酒店简介
        $hdata['h_des'] = I('des');//酒店描述
        $tickImg = I('post.hoteldetail');
        $path = "./Public/hotel/hotel/";

        $bifile = array();
        foreach ($tickImg as $k => $t) {
            foreach ($t['srcArr'] as $m => $bi) {
                $bifile[$m]['src'] = $this->addr($bi['src'], $path, $k . $m);
                if (empty($bifile[$m]['src'])) {
                    $arr = parse_url($bi['src']);
                    $bifile[$m]['src'] = $arr['path'];
                }
                $bifile[$m]['imgtitle'] = $bi['imgtitle'];
            }
            $file[$k]['srcArr'] = $bifile;
            $file[$k]['des'] = $t['imgdescribe'];
            $file[$k]['addImgshow'] = $t['addImgshow'];
            $bifile = array();
        }

        $hdata['h_img'] = json_encode($file);
        $result = M('hotel')->where('h_code=' . $code . " and h_user_id=" . $this->userId)->save($hdata);
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '更新失败'));
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '更新成功'));
    }

    /**
     * 删除酒店信息
     */
    public function delHotel()
    {
        $code = I('post.Code');
        $result = M('hotel')->where('h_code=' . $code . " and h_user_id=" . $this->userId)->delete();
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '删除失败'));
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '删除成功'));
    }


    /**
     * 新建景酒套餐
     */
    public function newViewFood()
    {
        $contact = M('contact')->where("c_user_id = " . $this->userId)->select();
        $viewList = M('view')->where("v_user_id = " . $this->userId)->select();
        $foodList = M('food')->where("f_user_id = " . $this->userId)->select();
        $hotelList = M('hotel')->where("h_user_id = " . $this->userId)->select();
        $info['view'] = $viewList;
        $info['food'] = $foodList;
        $info['hotel'] = $hotelList;
        $info['contact'] = $contact;
        $this->ajaxReturn($info);
    }

    /**
     * 保存套餐产品
     */
    public function addViewFood()
    {
        $bdata['s_product_id'] = I('post.productId');//供应商编号
        $bdata['s_tag'] = json_encode(I('post.tagArr'));//标签
        $bdata['s_available'] = I('post.availableProject');//可选项目
        $bdata['s_goods_name'] = I('post.goodsname');//商品名称


        $bdata['s_code'] = $this->createCode();//商品编码
        $bdata['s_add_type'] = I('post.type');//产品类型 1 手动上单 2手动上单直连 3 接口上单、
        $bdata['s_settle_model'] = I('post.settleModel');//佣金模式 低价模式
        $bdata['s_category'] = I('post.category');//合同编号
        $bdata['s_rate'] = I('post.rate');//佣金比例
        $bdata['s_view'] = json_encode(I('post.view'));//景点编码
        $bdata['s_food'] = json_encode(I('post.food'));//餐饮编码
        $bdata['s_hotel_day'] = I('post.day');//间夜
        $bdata['s_man_num'] = I('post.mNum');//成人输俩
        $bdata['s_child_num'] = I('post.cNum');//儿童数量
        $bdata['s_hotel_mark_price'] = I('post.mPrice');//市场价格
        $bdata['s_hotel_settle_price'] = I('post.sPrice');//结算价格
        $bdata['s_hotel_plane_price'] = I('post.pPrice');//平台价格
        $bdata['s_hotel_t_info'] = json_encode(I('post.tInfo'));//行程信息
        $bdata['s_hotel_yd_time'] = I('post.yTime');//提前预定时间
        $bdata['s_hotel_sure_time'] = I('post.sureTime');//确认时间
        $bdata['s_hotel_buy_m_num'] = I('post.mbNum');//最小购分数
        $bdata['s_hotel_buy_b_num'] = I('post.bbNum');//最大购分数(0为无限制)
        $bdata['s_tk_info'] = I('post.tkInfo');//退款说明
        $bdata['s_yq_ts'] = I('post.ts');//友情提示
        $bdata['s_use_info'] = I('post.uInfo');//使用说明
        $bdata['s_sj_time'] = I('post.sTime');//上架时间
        $bdata['s_xj_time'] = I('post.xTime');//下架时间
        $bdata['s_play_person_info'] = I('post.personInfo');//是否需要游玩人信息
        $bdata['s_tj_ly'] = json_encode(I('post.ly'));//推介理由
        $bdata['s_type'] = 4;//审核状态 1提交审核 4 制作中
        $bdata['s_name'] = I('post.name');//商品名称
        $bdata['s_yd_time'] = I('post.yDay');//预定时间
        $bdata['s_user_id'] = $this->userId;
        $bdata['s_create_time'] = time();
        $tickImg = I('post.img');
        $path = "./Public/hotel/tc/";
        foreach ($tickImg as $k => $t) {
            $file[$k]['imgtitle'] = $this->addr($t['src'], $path, $k);
            $file[$k]['headImg'] = $t['headImg'];
        }
        $bdata['s_img'] = json_encode($file);//图片信息

        $ri = I('post.rl');
        $tickType = I('post.datatype');//有效期模式 1 有效期 2 价格日历
        M('scenery')->add($bdata);
        if ($tickType == 0) {
            $this->addLine($ri, $bdata['s_code']);
        } elseif ($tickType == 1) {
            $this->addPriceCa($ri, $bdata['s_code']);
        }
    }

    /**
     * 更新套餐产品
     */
    public function saveViewFood()
    {
        $code = I('code');//商品编码

        $bdata['s_product_id'] = I('post.productId');//供应商编号
        $bdata['s_tag'] = json_encode(I('post.tagArr'));//标签
        $bdata['s_available'] = I('post.availableProject');//可选项目
        $bdata['s_goods_name'] = I('post.goodsname');//商品名称

        $bdata['s_type'] = I('post.type');//产品类型 1 手动上单 2手动上单直连 3 接口上单、
        $bdata['s_settle_model'] = I('settleModel');//佣金模式 低价模式
        $bdata['s_category'] = I('post.category');//合同编号
        $bdata['s_rate'] = I('post.rate');//佣金比例
        $bdata['s_view'] = json_encode(I('post.view'));//景点编码
        $bdata['s_food'] = json_encode(I('post.food'));//餐饮编码
        $bdata['s_hotel_day'] = I('post.day');//间夜
        $bdata['s_man_num'] = I('post.mNum');//成人数量
        $bdata['s_child_num'] = I('post.cNum');//儿童数量
        $bdata['s_hotel_mark_price'] = I('post.mPrice');//市场价格
        $bdata['s_hotel_settle_price'] = I('post.sPrice');//结算价格
        $bdata['s_hotel_plane_price'] = I('post.pPrice');//平台价格
        $bdata['s_hotel_t_info'] = json_encode(I('post.tInfo'));//行程信息
        $bdata['s_hotel_yd_time'] = I('post.yTime');//提前预定时间
        $bdata['s_hotel_sure_time'] = I('post.sureTime');//确认时间
        $bdata['s_hotel_buy_m_num'] = I('post.mbNum');//最小购分数
        $bdata['s_hotel_buy_b_num'] = I('post.bbNum');//最大购分数(0为无限制)
        $bdata['s_tk_info'] = I('post.tkInfo');//退款说明
        $bdata['s_yq_ts'] = I('post.ts');//友情提示
        $bdata['s_use_info'] = I('post.uInfo');//使用说明
        $bdata['s_sj_time'] = I('post.sTime');//上架时间
        $bdata['s_xj_time'] = I('post.xTime');//下架时间
        $bdata['s_tj_ly'] = json_encode(I('post.ly'));//推介理由
        $bdata['s_type'] = 4;//推介理由
        $bdata['s_name'] = I('post.name');//商品名称
        $bdata['s_create_time'] = time();
        $bdata['s_yd_time'] = I('post.yDay');//预定时间
        $tickImg = I('post.img');
        $path = "./Public/hotel/tc/";
        foreach ($tickImg as $k => $t) {
            $imgFile = $this->addr($t['src'], $path, $k);
            if (empty($imgFile)) {
                $arr = parse_url($t['src']);
                $file[$k]['imgtitle'] = $arr['path'];
            } else {
                $file[$k]['imgtitle'] = $imgFile;
            }
            $file[$k]['headImg'] = $t['headImg'];
        }
        $bdata['s_img'] = json_encode($file);//图片信息
        M('scenery')->where('s_code=' . $code . " and s_user_id=" . $this->userId)->save($bdata);
        $ri = I('post.rl');
        $tickType = I('post.datatype');//有效期模式 1 有效期 2 价格日历
        if ($tickType == 0) {
            $this->addLine($ri, $code);
        } elseif ($tickType == 1) {
            $this->addPriceCa($ri, $code);
        }
    }

    /**
     * 景酒套餐有效期模式
     */
    public function addLine($ri, $code)
    {
        $tickType = 1;//有效期模式 1 有效期 2 价格日历
        $data['s_tick_date'] = $tickType;//价格模式
        M('scenery')->where(array('s_code' => $code, 's_user_id' => $this->userId))->save($data);
        M('scenery_yx')->where(array('y_code' => $code, 'y_user_id' => $this->userId))->delete();

        foreach ($ri as $i) {
            $ydata['y_b_time'] = $i['date'];//有效期开始时间
            $ydata['y_can_use_time'] = $i['cdate'];//可用时间c
            $ydata['y_no_user_time'] = $i['ndate'];//不可用时间c
            $ydata['y_mark_price'] = $i['marketprice'];//价格
            $ydata['y_my_price'] = $i['platformprice'];//价格
            $ydata['y_js_price'] = $i['closeprice'];//价格
            $ydata['y_kc'] = $i['repertory'];//库存
//            $price = M('scenery_yx')->where(array('y_code' => $code, 'y_user_id' => $this->userId, 'y_b_time' => $i['date']))->find();
//            if (!$price) {
            $ydata['y_code'] = $code;
            $ydata['y_user_id'] = $this->userId;
            $price = M('scenery_yx')->add($ydata);
//            } else {
//                $price = M('scenery_yx')->where(array('y_code' => $code, 'y_user_id' => $this->userId, 'y_b_time' => $i['date']))->save($ydata);
//            }
            if (!$price) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '操作失败'));
            }
        }

        $this->ajaxReturn(array('code' => '1', 'msg' => '操作成功'));

    }

    /**
     * 景酒套餐价格日历模式
     */
    public function addPriceCa($ri, $code)
    {
        $tickType = 2;//有效期模式 1 有效期 2 价格日历
        M('scenery')->where(array('s_code' => $code, 's_user_id' => $this->userId))->save(array('s_tick_date' => $tickType));
        M("seceny_price")->where(array('p_code' => $code, 'p_user_code' => $this->userId))->delete();
        foreach ($ri as $i) {
            $date = $i['date'];//时间
            $data['p_js_price'] = $i['closeprice'];//结算价格
            $data['p_mark_price'] = $i['marketprice'];//结算价格
            $data['p_my_price'] = $i['platformprice'];//结算价格
            $data['p_ck'] = $i['repertory'];//库存
//            $price = M("seceny_price")->where(array('p_date' => $date, 'p_code' => $code, 'p_user_code' => $this->userId))->find();
//            if (!$price) {
            $data['p_code'] = $code;
            $data['p_date'] = $date;
            $data['p_user_code'] = $this->userId;
            $result = M('seceny_price')->add($data);
//            }
//            else {
//                $result = M('seceny_price')->where((array('p_date' => $date, 'p_code' => $code, 'p_user_code' => $this->userId)))->save($data);
//            }
            if (!$result) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '操作失败'));
            }
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '操作成功'));
    }

    /**
     * 获取价格模式
     */
    public function getPriceModel()
    {
        $type = I('dataType');
        $code = I('post.code');
        if ($type == 2) {
            $result = M("seceny_price")->where(array('y_code' => $code, 'p_user_code' => $this->userId))->select();
        } else {
            $result = M('scenery_yx')->where(array('p_code' => $code, 'y_user_id' => $this->userId))->select();
        }
        $return['type'] = $type;
        $return['info'] = $result;
        $this->ajaxReturn($result);
    }

    /**
     * 获取产品列表
     */
    public function getViewFoodList()
    {
        $page = I('post.page');
        if (empty($page)) {
            $page = 0;
        } else {
            $page--;
        }

        //状态
        if (!empty(I('post.type'))) {
            $where['s_type'] = I('post.type');
        }
        //商品名称
        if (!empty(I('post.name'))) {
            $name = I('post.name');
            $where['s_name'] = array('like', "%$name%");
        }
        if ($_SESSION['type'] == 1) {
            $where['s_user_id'] = $this->userId;
        }

        $where['s_is_del'] = array('neq', 1);
        $count = M('scenery')->where($where)->count();
        $list = M('scenery')->field('s_code,s_id,s_sj_time,s_xj_time,s_type,s_is_pass_log,s_name')->where($where)->limit($page * 10, 10)->order('s_create_time desc')->select();
        if ($_SESSION['type'] == 1) {
            foreach ($list as &$item) {
                if ($item['s_type'] == 1) {
                    $item['button'] = '审核中';
                    $item['type'] = "审核中请等待";
                } elseif ($item['s_type'] == 2) {
                    $item['button'] = '上线';
                    $item['type'] = '审核通过可以上线';
                } elseif ($item['s_type'] == 3) {
                    $item['button'] = '审核失败';
                    $item['type'] = $item['s_is_pass_log'];
                } elseif ($item['s_type'] == 5) {
                    $item['button'] = '下线';
                    $item['type'] = '上线售卖中';
                } elseif ($item['s_type'] == 4) {
                    $item['button'] = '提交审核';
                    $item['type'] = '产品可以提交审核';
                } elseif ($item['s_type'] == 6) {
                    $item['button'] = '产品已下线';
                    $item['type'] = '产品重新编辑提交审核';
                }
            }
        }

        if ($_SESSION['type'] == 2) {
            foreach ($list as &$item) {
                if ($item['s_type'] == 1) {
                    $item['button'] = '审核通过/审核不通过';
                    $item['type'] = "请审核";
                } elseif ($item['s_type'] == 2) {
                    $item['button'] = '等待供应商上线';
                    $item['type'] = '等待供应商上线';
                } elseif ($item['s_type'] == 3) {
                    $item['button'] = '审核不通过';
                    $item['type'] = $item['s_is_pass_log'];
                } elseif ($item['s_type'] == 5) {
                    $item['button'] = '下线';
                    $item['type'] = '上线售卖中';
                } elseif ($item['s_type'] == 4) {
                    $item['button'] = '供应商编辑中';
                    $item['type'] = '正在编辑';
                } elseif ($item['s_type'] == 6) {
                    $item['button'] = '产品已下线';
                    $item['type'] = '产品重新编辑提交审核';
                }
            }
        }

        $return['page'] = $count;
        $return['info'] = $list;
        $this->ajaxReturn($return);
    }

    /**
     * 提交审核
     */
    public function toExam()
    {
        $code = I('post.code');
        $se = M('scenery')->field('s_type')->where(array('s_code' => $code, 's_user_id' => $this->userId))->find();
        if (!$se || $se['s_type'] != 4) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $result = M('scenery')->where(array('s_code' => $code, 's_user_id' => $this->userId))->save(array('s_type' => 1));
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '提交失败'));
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '提交成功'));
    }

    /**
     * 提交上线
     */
    public function onSeceney()
    {
        $code = I('post.code');
        $se = M('scenery')->field('s_type')->where(array('s_code' => $code, 's_user_id' => $this->userId))->find();
        if (!$se || $se['s_type'] != 2) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $result = M('scenery')->where(array('s_code' => $code, 's_user_id' => $this->userId))->save(array('s_type' => 5));
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '提交失败'));
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '提交成功'));
    }

    /**
     * 下线
     */
    public function downSeceney()
    {
        $code = I('post.code');
        if ($_SESSION['type'] == 1) {
            $se = M('scenery')->field('s_type')->where(array('s_code' => $code, 's_user_id' => $this->userId))->find();

        }
        if ($_SESSION['type'] == 2) {
            $se = M('scenery')->field('s_type')->where(array('s_code' => $code))->find();
        }
        if (!$se || $se['s_type'] != 5) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        if ($_SESSION['type'] == 1) {
            $result = M('scenery')->where(array('s_code' => $code, 's_user_id' => $this->userId))->save(array('s_type' => 6));

        }

        if ($_SESSION['type'] == 2) {
            $result = M('scenery')->where(array('s_code' => $code))->save(array('s_type' => 6));

        }
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '提交失败'));
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '提交成功'));
    }

    /**
     * 获取当个产品详细信息
     */
    public function getOnlyViewFood()
    {
        $code = I('post.code');
        $list = M('scenery')->where(array('s_code' => $code))->find();
        if ($list['s_tick_date'] == 1) {
            if ($_SESSION['type'] == 1) {
                $rl = M('scenery_yx')->where(array('y_code' => $code, 'y_user_id' => $this->userId))->select();
            }
            if ($_SESSION['type'] == 2) {
                $rl = M('scenery_yx')->where(array('y_code' => $code))->select();
            }

        } elseif ($list['s_tick_date'] == 2) {
            if ($_SESSION['type'] == 1) {
                $rl = M('seceny_price')->where(array('p_code' => $code, 'p_user_code' => $this->userId))->select();

            }
            if ($_SESSION['type'] == 2) {
                $rl = M('seceny_price')->where(array('p_code' => $code))->select();

            }
        }
        $c = M('contact')->field('c_name')->where(array('c_id' => $list['s_category']))->find();


        $list['s_img'] = json_decode($list['s_img'], true);
        foreach ($list['s_img'] as &$item) {
            $item['src'] = C('img_url') . $item['imgtitle'];
            if ($item['headImg'] === 'false') {
                $item['headImg'] = false;
            } else {
                $item['headImg'] = true;
            }
        }
        $list['s_hotel_t_info'] = json_decode($list['s_hotel_t_info'], true);
        $list['s_view'] = json_decode($list['s_view'], true);
        $list['s_food'] = json_decode($list['s_food'], true);
        $list['s_tag'] = json_decode($list['s_tag'], true);
        $list['tInfo'] = json_decode($list['tInfo'], true);
        $list['s_tj_ly'] = json_decode($list['s_tj_ly'], true);
        if ($list['s_available'] === 'false') {
            $list['s_available'] = false;
        } else {
            $list['s_available'] = true;
        }
        foreach ($list['s_hotel_t_info'] as &$item) {
            if ($item['hotelListshow'] === 'true') {
                $item['hotelListshow'] = true;
            } else {
                $item['hotelListshow'] = false;
            }

            if ($item['roomListshow'] === 'true') {
                $item['roomListshow'] = true;
            } else {
                $item['roomListshow'] = false;
            }
        }

        foreach ($list['s_tag'] as &$st) {
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
        $list['c_name'] = $c['c_name'];
//        $list['v_name'] = $v['v_name'];
//        $list['f_name'] = $f['f_name'];
        $return['list'] = $list;
        $return['rl'] = $rl;
        $this->ajaxReturn($return);
    }

    /**
     *删除
     */
    public function delViewFood()
    {
        $code = I('post.code');
        $result = M('scenery')->where(array('s_code' => $code, 's_user_id' => $this->userId))->save(array('s_is_del' => '1'));
        if (!$result) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '删除失败'));
        }
        $this->ajaxReturn(array('code' => 1, 'msg' => '删除成功'));
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
        if ($_SESSION['type'] == 1) {
            $where['o_user_id'] = $this->userId;
        }

        if (!empty(I('post.type'))) {
            $where['o_order_type'] = I('post.type');//订单情况 1 已消费 2 未消费 3 关闭取消订单 4 待付款 5退款中 6 退款成功 7 拒绝退款 8待确认订单
        }
        if (!empty(I('post.orderSn'))) {
            $where['o_order_sn'] = I('post.orderSn');
        }
        if (!empty(I('post.mobile'))) {
            $where['o_mobile'] = I('pos.mobile');
        }
        if (!empty(I('post.bdate'))) {
            $where['unix_timestamp(o_date)'] = array(array("egt", strtotime(I('pos.bdate')) . " 00:00:00"), array('elt', strtotime(I('pos.edate') . " 23:59:59")));
        }

        if (empty($where)) {
            $list = M('seceny_order')->limit($page * 10, 10)->order('o_id desc')->select();
        } else {
            $list = M('seceny_order')->where($where)->limit($page * 10, 10)->order('o_id desc')->select();
        }

        foreach ($list as &$o) {
            $o['order_action'] = $this->returnOrderAction()[$o['o_order_type']];
        }
        $count = M('seceny_order')->where($where)->count();
        $return['page'] = $count;
        $return['info'] = $list;
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
//        $orderType = M('seceny_order')->where('o_order_sn =' . $order_sn)->getField('o_order_type');
        $orderInfo = M('seceny_order')
//            ->field("o_order_type,o_jxs_code,o_order_price,o_js_price,o_num")
            ->where(array("o_order_sn" => $order_sn))->find();
        //订单状态 2未消费
        if(empty($orderInfo) || $orderInfo["o_order_type"] != "2"){
            $this->ajaxReturn(array('code' => '0', 'msg' => '订单错误'));
        }

        //没有经销商的情况 todo 没有经销商 账单清单怎么算
        if(empty($orderInfo["o_jxs_code"])){
            $result = M('seceny_order')->where(array('o_order_sn' => $order_sn, 'o_user_id' => $user_id))->save(array('o_order_type' => '1', 'o_user_time' => time()));
            if (!$result) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '订单错误'));
            }
            $this->ajaxReturn(array('code' => '1', 'msg' => '操作成功'));
        }

        //经销商不存在的情况
        $jxs_moneyInfo = M('jxs_money')->where(array("jxs_code" => $orderInfo["o_jxs_code"]))->find();
        if(empty($jxs_moneyInfo)){
            $this->ajaxReturn(array('code' => '0', 'msg' => '经销商账户不存在'));
        }

        //经销商佣金 = 订单总价格 - 结算价格 * 分数
        $jxsYJ = $orderInfo["o_order_price"] - ($orderInfo["o_js_price"] * $orderInfo["o_num"]);

        $jxs_bill_check = M('jxs_bill')->where(array("tb_jxs_code" => $orderInfo["o_jxs_code"],"tb_code" => "1","tb_order_id" => $order_sn))->find();
        //账单表里有记录 错误情况
        if($jxs_bill_check){
            $ModelOne = M();           // 实例化一个空对象
            $ModelOne->startTrans();  // 开启事务
            $omOne = $ModelOne->table('lf_seceny_order')->where(array('o_order_sn' => $order_sn, 'o_user_id' => $user_id))->save(array('o_order_type' => '1', 'o_user_time' => time()));
            //账单表添加记录
            $saveBillOne["tb_order_id"] = $order_sn;                            //订单编号
            $saveBillOne["tb_jxs_code"] = $orderInfo["o_jxs_code"];            //经销商code
            $saveBillOne["tb_money"] = $jxsYJ;                                  //进账金额
            $saveBillOne["tb_type"] = "scenery";                               //订单类型
            $saveBillOne["tb_code"] = "6";                                      //状态 6异常
            $saveBillOne["tb_balance"] = $jxs_moneyInfo["jxs_no_money"];       //账户余额  未加
            $saveBillOne["tb_time"] = date("Y-m-d H:i:s", time());              //时间
            $saveBillOne["tb_remark_info"] = "数据库已有进账数据";              //备注
            $gmOne = $ModelOne->table("lf_jxs_bill")->where(array("tb_jxs_code" => $orderInfo["o_jxs_code"]))->data($saveBillOne)->add();
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
        $om = $Model->table('lf_seceny_order')->where(array('o_order_sn' => $order_sn, 'o_user_id' => $user_id))->save(array('o_order_type' => '1', 'o_user_time' => time()));
        //jxs_money 增加经销商总金额
        $jxs_no_money = $jxs_moneyInfo["jxs_no_money"] + $jxsYJ;                               //未提现金额
        $jxs_all_money = $jxs_moneyInfo["jxs_already_money"] + $jxs_no_money;                 //总金额
        $pm = $Model->table("lf_jxs_money")->where(array("jxs_code" => $orderInfo["o_jxs_code"]))->save(array('jxs_no_money' => $jxs_no_money, 'jxs_all_money' => $jxs_all_money));
        //账单表添加记录
        $saveBill["tb_order_id"] = $order_sn;                            //订单编号
        $saveBill["tb_jxs_code"] = $orderInfo["o_jxs_code"];            //经销商code
        $saveBill["tb_money"] = $jxsYJ;                                   //进账金额  已加
        $saveBill["tb_type"] = "scenery";                                //订单类型
        $saveBill["tb_code"] = "1";                                      //状态 1进账
        $saveBill["tb_balance"] = $jxs_no_money;                         //账户余额
        $saveBill["tb_time"] = date("Y-m-d H:i:s", time());             //时间
        $gm = $Model->table("lf_jxs_bill")->where(array("tb_jxs_code" => $orderInfo["o_jxs_code"]))->data($saveBill)->add();
        if ($om && $pm && $gm) {
//           $Model->rollBack();
            $Model->commit();
            $this->ajaxReturn(array('code' => '1', 'msg' => '操作成功'));
        } else {
            $Model->rollBack();
            $this->ajaxReturn(array('code' => '0', 'msg' => '操作失败，请联系管理员'));
        }


//        if ($orderType == '2') {
//            $result = M('seceny_order')->where(array('o_order_sn' => $order_sn, 'o_user_id' => $user_id))->save(array('o_order_type' => '1', 'o_user_time' => time()));
//            if ($result) {
//                $this->ajaxReturn(array('code' => '1', 'msg' => '操作成功'));
//            } else {
//                $this->ajaxReturn(array('code' => '0', 'msg' => '订单错误'));
//            }
//        } else {
//            $this->ajaxReturn(array('code' => '0', 'msg' => '订单错误'));
//        }
    }

    /**
     * 同意退款
     */
    public function agreeRefund()
    {
        $order_sn = I('post.orderSn');
        $user_id = $this->userId;//供应商Id
        $orderType = M('seceny_order')->where('o_order_sn =' . $order_sn)->getField('o_order_type');
        if ($orderType == '5') {
            $result = M('seceny_order')->where(array('o_order_sn' => $order_sn, 'o_user_id' => $user_id))->save(array('o_order_type' => '6'));
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
        $orderType = M('seceny_order')->where('o_order_sn =' . $order_sn)->getField('o_order_type');
        if ($orderType == '5') {
            $result = M('seceny_order')->where(array('o_order_sn' => $order_sn, 'o_user_id' => $user_id))->save(array('o_order_type' => '7'));
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
        $orderType = M('seceny_order')->where('o_order_sn =' . $order_sn)->getField('o_order_type');
        if ($orderType == '8') {
            $result = M('seceny_order')->where(array('o_order_sn' => $order_sn, 'o_user_id' => $user_id))->save(array('o_order_type' => '2', 'o_sure_time' => date('Y-m-d H:i:s', time())));
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
        $type = I('post.type');
        if ($type == 1) {
            $where['y_code'] = $code;
            $where['y_user_id'] = $id;
            $where['unix_timestamp(y_b_time)'] = strtotime($date);
           $result = M('scenery_yx')->where($where)->save(array('y_is_open' => 1));
        } else {
            $where['p_code'] = $code;
            $where['p_user_code'] = $id;
            $where['unix_timestamp(p_date)'] = strtotime($date);
           $result = M('seceny_price')->where($where)->save(array('p_is_open' => 1));
        }
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
        $type = I('post.type');
        if ($type == 1) {
            $where['y_code'] = $code;
            $where['y_user_id'] = $id;
            $where['unix_timestamp(y_b_time)'] = strtotime($date);
           $result = M('scenery_yx')->where($where)->save(array('y_is_open' => 2));
        } else {
            $where['p_code'] = $code;
            $where['p_user_code'] = $id;
            $where['unix_timestamp(p_date)'] = strtotime($date);
         $result =    M('seceny_price')->where($where)->save(array('p_is_open' => 2));
        }
 if ($result) {
            $this->ajaxReturn(array('code' => 1, 'type' => 1));
        }
    }


    /**
     * 景酒套餐订单详情
     */
    public function getOrderInfo()
    {
        $orderSn = I('post.orderSn');
        if (empty($orderSn)) {
            $this->ajaxReturn(array('code' => 0, 'msg' => '非法操作'));
        }

        $info = M('seceny_order')->where(array('o_order_sn' => $orderSn))->find();
        $info['o_order_play_info'] = json_decode($info['o_order_play_info'], true);
        $this->ajaxReturn($info);
    }

    /**
     * 获取账单
     */
    public function getSceneryBill()
    {
        $btime = strtotime(I('post.btime') . " 00:00:00");
        $etime = strtotime(I('post.etime') . " 23:59:59");
        if (!empty(I('post.btime'))) {
            $where['s_b_time'] = array('egt', $btime);
            $where['s_e_time'] = array('elt', $etime);
        }

        if ($_SESSION['type'] == 1) {
            $where['s_user_id'] = $this->userId;
        }


        $billList = M('seceny_bill')->where($where)->select();
        foreach ($billList as &$bl) {
            $bl['s_b_time'] = date('Y-m-d', $bl['s_b_time']);
            $bl['s_e_time'] = date('Y-m-d', $bl['s_e_time']);
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
        $swhere['o_user_time'] = array(array('egt', $beginDate), array('lt', $endDate));
        $swhere['o_order_type'] = 1;
        if ($_SESSION['type'] == 1) {
            $swhere['o_user_id'] = $this->userId;
        }

        if ($_SESSION['type'] == 2) {
            $swhere['o_user_id'] = I('post.uid');
        }
        $groupBill = M('seceny_order')->where($swhere)->select();
        $this->ajaxReturn($groupBill);
    }
}
