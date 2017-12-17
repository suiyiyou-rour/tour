<?php
/**
 * Created by zhang.
 * User: zhang
 * Date: 2017/10/21
 * Time: 19:34
 */

namespace Home\Controller;


class AdminController extends BaseController
{

    /**
     * 合同添加
     */
    public function addContact()
    {
        $data['c_name'] = I('post.tittle');//合同标题
        $data['c_user_id'] = I('post.gysId');//供应商id
        $data['c_bank'] = I('post.bankNum');//银行卡号
        $data['c_bank_name'] = I('post.bankName');//银行卡号
        $data['c_content'] = I('post.content');//内容
        $data['c_user_name'] = I('post.name');//内容
        $result = M('contact')->add($data);
        if (!$result) {
            $this->ajaxReturn(array('code' => 0));
        }
        $this->ajaxReturn(array('code' => 1));
    }

    public function getContact()
    {
        $result = M('contact')->select();
        $this->ajaxReturn($result);
    }

    /**
     * 门票审核产品
     */
    public function tickVerify()
    {
        $code = I('post.code');
        $type = I('post.type');
        if ($type == 1) {
            $ttype = 2;
        } else {
            $ttype = 3;
        }
        $log = '产品修改后重新提交';
        $tick = M('tick')->where(array('t_code' => $code))->find();
        if ($tick['t_tick_type'] != 1 || empty($tick)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $result = M('tick')->where(array('t_code' => $code))->save(array('t_tick_type' => $ttype, 't_tick_verify_log' => $log, 't_tick_verify_time' => date("Y-m-d", time())));
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '操作失败'));
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '操作成功'));
    }

    /**
     * 跟团游产品审核
     */
    public function groupVerify()
    {
        $code = I('post.code');
        $type = I('post.type');
        if ($type == 1) {
            $ttype = 2;
        } else {
            $ttype = 3;
        }
        $log = '产品修改后重新提交';
        $tick = M('group')->where(array('g_code' => $code))->find();
        if ($tick['g_is_pass'] != 1 || empty($tick)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $result = M('group')->where(array('g_code' => $code))->save(array('g_is_pass' => $ttype, 'g_is_pass_log' => $log, 'g_is_pass_time' => date("Y-m-d", time())));
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '操作失败'));
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '操作成功'));
    }

    /**
     *景酒套餐产品审核
     */
    public function viewFoodVerify()
    {
        $code = I('post.code');
        $type = I('post.type');
        if ($type == 1) {
            $ttype = 2;
        } else {
            $ttype = 3;
        }
        $log = '产品修改后重新提交';
        $tick = M('scenery')->where(array('s_code' => $code))->find();
        if ($tick['s_type'] != 1 || empty($tick)) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '非法操作'));
        }
        $result = M('scenery')->where(array('s_code' => $code))->save(array('s_type' => $ttype, 's_is_pass_log' => $log, 's_is_pass_time' => date("Y-m-d", time())));
        if (!$result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '操作失败'));
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '操作成功'));
    }

    /**
     * 门票生成账单
     */
    public function createTickBill()
    {
        $beginDate = strtotime(I('post.bdate') . " 00:00:00");
        $endDate = strtotime(I('post.edate') . " 23:59:59");
        if (empty($beginDate || $endDate)) {
            $this->ajaxReturn(array('code' => 0));
        }
//        $beginDate = strtotime('2017-01-01');
//        $endDate = strtotime('2017-12-01');
        $twhere['tb_e_time'] = array('egt', $beginDate);
        $result = M('tick_bill')->where($twhere)->select();
        if ($result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '开始日期要大于最近一次的结束日期'));
        }
        //门票账单
        $where['t_tick_use_time'] = array(array('egt', $beginDate), array('elt', $endDate));
        $where['t_tick_order_type'] = 1;
        $tickBill = M('tick_order')->field('t_tick_id,sum(t_tick_js_price *t_tick_num * (100-2)/100) as money,sum(t_tick_js_price *t_tick_num) as price')->where($where)->group('t_tick_id')->select();
        foreach ($tickBill as $i) {
            $data['tb_b_time'] = $beginDate;
            $data['tb_e_time'] = $endDate;
            $data['tb_a_money'] = number_format($i['money'], 2);
            $data['tb_user_id'] = $i['t_tick_id'];
            $data['tb_is_order_price'] = empty($i['price']) ? 0 : $i['price'];
            $data['tb_is_settle'] = 0;
            $result = M('tick_bill')->add($data);
            if (!$result) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '生成失败'));
            }
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '生成成功'));
    }

    /**
     * 景酒套餐生成账单
     */
    public function createSeceneyBill()
    {
        $beginDate = strtotime(I('post.bdate') . " 00:00:00");
        $endDate = strtotime(I('post.edate') . " 23:59:59");
//        $beginDate = strtotime('2017-01-01');
//        $endDate = strtotime('2017-12-01');
        $where['s_e_time'] = array('egt', $beginDate);
        $result = M('seceny_bill')->where($where)->select();
        if ($result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '开始日期要大于最近一次的结束日期'));
        }
        $swhere['o_user_time'] = array(array('egt', $beginDate), array('elt', $endDate));
        $swhere['o_order_type'] = 1;
        $secenyBill = M('seceny_order')->field('o_user_id,sum((o_js_price *o_num ) * (100-2)/100) as money,sum(o_js_price *o_num) as price')->where($swhere)->group('o_user_id')->select();
        foreach ($secenyBill as $s) {
            $data['s_b_time'] = $beginDate;
            $data['s_e_time'] = $endDate;
            $data['s_a_money'] = number_format($s['money'], 2);
            $data['s_user_id'] = $s['o_user_id'];
            $data['s_order_price'] = empty($s['price']) ? 0 : $s['price'];
            $data['s_is_settle'] = 0;
            $result = M('seceny_bill')->add($data);
            if (!$result) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '生成失败'));
            }
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '生成成功'));
    }

    /**
     * 跟团游账单
     */

    public function createGroupBill()
    {
        $beginDate = strtotime(I('post.bdate') . " 00:00:00");
        $endDate = strtotime(I('post.edate') . " 23:59:59");

//        $beginDate = strtotime('2017-01-01'." 00:00:00");
//        $endDate = strtotime('2017-12-01'."23:59:59");
        $where['g_e_time'] = array('egt', $beginDate);
        $result = M('group_bill')->where($where)->select();
        if ($result) {
            $this->ajaxReturn(array('code' => '0', 'msg' => '开始日期要大于最近一次的结束日期'));
        }
        $swhere['g_user_time'] = array(array('egt', $beginDate), array('elt', $endDate));
        $swhere['g_order_type'] = 1;//g_man_js_price  g_man_num g_child_num g_child_js_price g_dfc_num g_dfc_js_price
        $groupBill = M('group_order')->field('g_user_id,sum((g_man_js_price* g_man_num + g_child_num *g_child_js_price +  g_dfc_num * g_dfc_js_price) * (100-2)/100) as money,sum((g_man_js_price* g_man_num + g_child_num *g_child_js_price +  g_dfc_num * g_dfc_js_price)) as price')->where($swhere)->group('g_user_id')->select();
//        var_dump(M('group_order') ->_sql());exit;
        foreach ($groupBill as $s) {
            $data['g_b_time'] = $beginDate;
            $data['g_e_time'] = $endDate;
            $data['g_a_money'] = number_format($s['money'], 2);
            $data['g_user_id'] = $s['g_user_id'];
            $data['g_order_price'] = empty($s['price']) ? 0 : $s['price'];
            $data['g_is_settle'] = 0;
            $result = M('group_bill')->add($data);
            if (!$result) {
                $this->ajaxReturn(array('code' => '0', 'msg' => '生成失败'));
            }
        }
        $this->ajaxReturn(array('code' => '1', 'msg' => '生成成功'));
    }

    /**
     * 供应商生成
     */
    public function createGys()
    {
        $userId = $this->generate_username(6);
        $pwd = $this->create_password(9);
        $data['sp_account_num'] = $userId;
        $data['sp_pwd'] = md5($pwd);
        $data['sp_type'] = 1;
        $data['sp_open'] = 1;
        $result = M('sp')->add($data);
        if (!$result) {
            $this->ajaxReturn(array('code' => '0'));
        }
        $data['sp_pwd'] = $pwd;
        $this->ajaxReturn($data);
    }

    //自动为用户随机生成用户名(长度6-13)
    public function create_password($pw_length = 2)
    {
        $randpwd = '';
        for ($i = 0; $i < $pw_length; $i++) {
            $randpwd .= chr(mt_rand(33, 126));
        }
        return $randpwd;
    }

    public function generate_username($length = 6)
    {
        // 密码字符集，可任意添加你需要的字符
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            // 这里提供两种字符获取方式
            // 第一种是使用substr 截取$chars中的任意一位字符；
            // 第二种是取字符数组$chars 的任意元素
            // $password .= substr($chars, mt_rand(0, strlen($chars) - 1), 1);
            $password .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $password;
    }

    /**
     * 供应列表
     */
    public function getGysList()
    {
        $list = M('sp')->where(array('sp_type' => 1))->select();
        $this->ajaxReturn($list);
    }

    /**
     * 供应商开启
     */
    public function openGys()
    {
        $id = I('post.id');
        $result = M('sp')->where(array('sp_id' => $id))->save(array('sp_open' => 1));
        if (!$result) {
            $this->ajaxReturn(array('code' => '0'));
        }
        $this->ajaxReturn(array('code' => '1'));
    }

    /**
     * 供应商关闭
     */
    public function closeGys()
    {
        $id = I('post.id');
        $result = M('sp')->where(array('sp_id' => $id))->save(array('sp_open' => 0));
        if (!$result) {
            $this->ajaxReturn(array('code' => '0'));
        }
        $this->ajaxReturn(array('code' => '1'));
    }

    /**
     * 门票订单结算
     */
    public function settleOrder()
    {
        $id = I('post.id');
        $result = M('tick_bill')->where(array('tb_id' => $id))->save(array('tb_is_settle' => 1));
        if (!$result) {
            $this->ajaxReturn(array('code' => 0));
        }
        $this->ajaxReturn(array('code' => 1));
    }

    /**
     * 景就结算
     */
    Public function secenySettle()
    {
        $id = I('post.id');
        $result = M('seceny_bill')->where(array('s_id' => $id))->save(array('s_is_settle' => 1));
        if (!$result) {
            $this->ajaxReturn(array('code' => 0));
        }
        $this->ajaxReturn(array('code' => 1));
    }

    /**
     * 跟团游结算
     */
    public function groupSettle()
    {
        $id = I('post.id');
        $result = M('group_bill')->where(array('g_id' => $id))->save(array('g_is_settle' => 1));
        if (!$result) {
            $this->ajaxReturn(array('code' => 0));
        }
        $this->ajaxReturn(array('code' => 1));
    }

    /**
     * 查看合同信息
     */
    public function getGysInfo()
    {
        $id = I('post.id');
        $info = M('sp')->where(array('sp_id' => $id))->find();
        $info['sp_file'] = json_decode($info['sp_file'], true);
        foreach ($info['sp_file'] as &$f) {
            $f = C('img_url') . $f;
        }
        $this->ajaxReturn($info);
    }

    /**
     * 查看合同信息
     */
    public function getContactInfo()
    {
        $id = I('post.id');
        $info = M('contact')->where(array('c_id' => $id))->find();
        $info['c_content'] = html_entity_decode($info['c_content']);
        $this->ajaxReturn($info);
    }


    /**
     * 获取经销商列表
     */

    public function getJxsList()
    {
        $where['user_type'] = array('neq', 1);
        $info = M('user')->field('user_id,user_company,user_lx_mobile,user_email,user_address,user_fax,user_type')->where($where)->select();
        foreach ($info as &$item) {
            if ($item['user_type'] == 2) {
                $item['user_type_name'] = '审核通过';
            } elseif ($item['user_type'] == 3) {
                $item['user_type_name'] = '审核中';
            } elseif ($item['user_type'] == 4) {
                $item['user_type_name'] = '审核失败';
            }
        }
        $this->ajaxReturn($info);
    }

    public function getJxsInfo()
    {
        $id = I('post.id');
        $info = M('user')->find($id);
        $info['user_js_file'] = C('img_url') . $info['user_js_file'];
        $this->ajaxReturn($info);
    }

    public function shJsx()
    {
        $type = I('post.type');//成功为 2 失败为 4
        $id = I('post.id');
        $flag = M('user')->where(array('user_id' => $id))->save(array('user_type' => $type));
        if ($flag) {
            $this->ajaxReturn(array('code' => 1));
        } else {
            $this->ajaxReturn(array('code' => 0));
        }
    }
}