<?php
/**
 * 订单 以及详情
 */

namespace Weixin\Controller;
use Think\Controller;
class JxsCashController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        header("Content-Type:text/html;charset=utf-8");
        #todo 判断登陆状态
         if($this->checkJS()){
             return;
         };
//         $this->user_account="54";        //测试
         if (empty($_SESSION["online_use_info"]["user_id"])) {
             $this->ajaxReturn(array('code' => 403, "msg" => "没有登陆或者登陆超时"));
         }

    }

    //获取经销商余额
    public function getBalance(){
        $user_account = $_SESSION["online_use_info"]["user_id"];
        $jxs_no_money = M("jxs_money")->where(array("jxs_code"=>$user_account))->getField("jxs_no_money");
        if(empty($jxs_no_money)){
            $this->ajaxReturn(array('code' => 304, "msg" => "经销商查询错误"));
        }
        $this->ajaxReturn(array('code' => 200, "data" => array("balance" => $jxs_no_money)));
    }

    //账单详细列表
    public function getBillRecord(){
        $page = (int)I('page');
        $stateCode = I('stateCode');
        if (empty($page)) {
            $page = 0;
        } else {
            $page--;
        }
        if($stateCode){
            $where['tb_code']        =   array('eq', $stateCode);//状态值
        }else{
            $where['tb_code']        =   array('neq', '6');//排除异常状态
        }
        $where["tb_jxs_code"]   =   $_SESSION["online_use_info"]["user_id"];

        $res = M("jxs_bill")->where($where)->order("tb_id desc")->limit($page * 10, 10)->select();
        if(empty($res)){
            $this->ajaxReturn(array('code' => 304, "msg" => "经销商查询错误"));
        }
        $this->ajaxReturn(array('code' => 200, "data" => $res));
    }

    //提现请求 金额确认
    public function askCashConfirm(){
        $money = I('money');
        if(empty($money)){
            $this->ajaxReturn(array('code' => 304, "msg" => "参数错误"));
        }
        if($money < 100){
            $this->ajaxReturn(array('code' => 304, "msg" => "提现金额不能小于100"));
        }
        $jxs_money = M("jxs_money")->where(array("jxs_code"=>$_SESSION["online_use_info"]["user_id"]))->getField("jxs_no_money");
        if($money > $jxs_money){
            $this->ajaxReturn(array('code' => 304, "msg" => "提现金额不能大于账户余额，当前余额为".$jxs_money));
        }
        //计算手续费
        $data = $this->computeBrokerage($money);
        $dataMoney = $money - $data;            //提现金额
        $this->ajaxReturn(array('code' => 200, "data" => array("money" => $dataMoney,"cost"=>$data)));
    }

    //提现申请
    public function putInCash(){
        $money = I('money');
        $message = I('message');
        if(empty($money)){
            $this->ajaxReturn(array('code' => 304, "msg" => "参数错误"));
        }
        if($money < 100){
            $this->ajaxReturn(array('code' => 304, "msg" => "提现金额不能小于100"));
        }
        if($money > 100000){
            $this->ajaxReturn(array('code' => 304, "msg" => "提现金额不能大于100000"));
        }
        $jxs_code = $_SESSION["online_use_info"]["user_id"];
        $jxs_money = M("jxs_money")->where(array("jxs_code"=>$jxs_code))->getField("jxs_no_money");
        if($money > $jxs_money){
            $this->ajaxReturn(array('code' => 304, "msg" => "提现金额不能大于账户余额，当前余额为".$jxs_money));
        }

        $jxs_money = $jxs_money - $money;       //账户余额   先扣
        $JJmoney = 0 - $money;                  //进账金额

        $Model = M();           // 实例化一个空对象
        $Model->startTrans();  // 开启事务
        //经销商余额表
        $pm = $Model->table("lf_jxs_money")->where(array("jxs_code" => $jxs_code))->save(array('jxs_no_money' => $jxs_money));
        $saveBill["tb_jxs_code"] = $jxs_code;                             //经销商code
        $saveBill["tb_money"] = $JJmoney;                                 //进账金额
        $saveBill["tb_type"] = "extract";                                //订单类型
        $saveBill["tb_code"] = "4";                                      //状态 4
        $saveBill["tb_balance"] = $jxs_money;                            //账户余额
        $saveBill["tb_time"] = date("Y-m-d H:i:s", time());              //时间
        $saveBill["tb_jxs_message"] = $message;                          //经销商备注
        //应该提现的金额
        $procedureFee = $this->computeBrokerage($money);                       //手续费
        $cashMoney = $money - $procedureFee;                                   //提现的金额
        $saveBill["tb_remark_info"] = "提现实际金额是".$cashMoney.",手续费为".$procedureFee.",请确认！";  //平台备注
        $gm = $Model->table("lf_jxs_bill")->where(array("tb_jxs_code" => $jxs_code))->data($saveBill)->add();
        if($pm && $gm){
            $Model->commit();
            $this->ajaxReturn(array('code' => 200, 'msg' => '操作成功'));
        } else {
            $Model->rollBack();
            $this->ajaxReturn(array('code' => 403, 'msg' => '操作失败，请联系管理员'));
        }
    }

    //计算佣金
    private function computeBrokerage($money){
        if(100 <= $money && $money < 1000){
            $data = $money * 0.1 ;
            $data = round($data,2);
        }else if(1000 <= $money && $money < 3000){
            $data = $money * 0.08 ;
            $data = round($data,2);
        }else if(3000 <= $money && $money < 5000){
            $data = $money * 0.07 ;
            $data = round($data,2);
        }else if(5000 < $money){
            $data = $money * 0.06 ;
            $data = round($data,2);
        }
        return $data;
    }


    //判断自己是不是经销商
    private function checkJS(){
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