<?php
/**
 *  页面以及 列表接口
 */
namespace Page\Controller;
use Think\Controller;
class HomeController extends Controller {
    public function __construct(){
        parent::__construct();
        $pass = array('login','index');
        if(!in_array(ACTION_NAME,$pass)){
            if(empty(session('UserAdminLogin'))){
                $this->display('home/index');
                die;
            }
        }

    }

    /*主页面显示*/
    public function index(){
        $this->display('home/index');
    }

    public function login(){
        $user = I("post.user");
        $pwd = I("post.pwd");
        if(empty($user) || empty($pwd)){
            $this->error('账号密码不能为空','index',1);
        }
        if($user != "syy" || $pwd !="syy123" ){
            $this->error('账号密码不能为空','index',1);
        }
        $res = session('UserAdminLogin',1);
        $this->redirect('home/show');
    }

    public function show(){
        $this->display('home/back');
    } 

    public function showExtract(){
        $state = I("state");

        if($state == 3){
            $where['tb_code'] = 3;
        }elseif($state == 5){
            $where['tb_code'] = 5;
        }else {
            $where['tb_code'] = 4;
        }
        $where['tb_type'] = 'extract';
        M('jxs_bill');
        $count = M('jxs_bill')->where($where)->count("tb_id");// 查询满足要求的总记录数
        $page = new \Org\Custom\Page($count,5);
        $show  = $page->show();// 分页显示输出
        $list = M("jxs_bill")
                ->alias("a")
                ->join("lf_user as b ON a.tb_jxs_code = b.user_id")
                ->field("a.*,b.user_company")
                ->order('tb_id desc')
                ->where($where)
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
        $this->assign('list',$list);// 赋值数据集
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('status',$where['tb_code']); //状态
        $this->display("home/showExtract");
    }

    //提现确认
    public function extractYes(){
        $id = I("id");
        if(empty($id)){
            $this->ajaxReturn(array('code' => 404, 'msg' => '参数错误'));
        }
        $where['tb_id'] = $id;
        $where['tb_code'] = '4';
        $where['tb_type'] = 'extract';
        $res = M("jxs_bill")->where($where)->find();
        if(!$res){
            $this->ajaxReturn(array('code' => 304, 'msg' => '提现请求数据不存在'));
        }
        if(empty($id)){
            $this->ajaxReturn(array('code' => 404, 'msg' => '参数错误'));
        }
        $where['tb_id'] = $id;
        $where['tb_code'] = '4';
        $where['tb_type'] = 'extract';
        $res = M("jxs_bill")->where($where)->find();
        if(!$res){
            $this->ajaxReturn(array('code' => 304, 'msg' => '提现请求数据不存在'));
        }
        $jxscode = $res["tb_jxs_code"];
        $mres = M("jxs_money")->where(array("jxs_code" => $jxscode))->find();
        if(!$mres){
            $this->ajaxReturn(array('code' => 304, 'msg' => '提现请求账户不存在'));
        }

        $money = (float)$res["tb_money"];   //提现金额
        $money = abs($money);       //正数
        $already_money = (float)$mres["jxs_already_money"]; //账户已提现
        $all = $money + $already_money; //总 已提现

        $Model = M();           // 实例化一个空对象
        $Model->startTrans();  // 开启事务
        //更新账单表提现状态
        $om = $Model->table('lf_jxs_bill')->where($where)->data(array("tb_code" => '3'))->save();
        //经销商账户表
        $pm = $Model->table('lf_jxs_money')->where(array("jxs_code" => $jxscode))->data(array("jxs_already_money" => $all))->save();
        if ($om && $pm ) {
            $Model->commit();
            $this->ajaxReturn(array('code' => '200', 'msg' => '操作成功'));
        } else {
            $Model->rollBack();
            $this->ajaxReturn(array('code' => '403', 'msg' => '操作失败，请联系管理员'));
        }
    }
     //提现拒绝
     public function extractRefuse(){
        $id = I("id");
        if(empty($id)){
            $this->ajaxReturn(array('code' => 404, 'msg' => '参数错误'));
        }
        $where['tb_id'] = $id;
        $where['tb_code'] = '4';
        $where['tb_type'] = 'extract';
        $res = M("jxs_bill")->where($where)->find();
        if(!$res){
            $this->ajaxReturn(array('code' => 304, 'msg' => '提现请求数据不存在'));
        }
        $res = M("jxs_bill")->where($where)->data(array("tb_code" => '5'))->save();
        if($res){
            $this->ajaxReturn(array('code' => '200', 'msg' => '操作成功'));
        }
        $this->ajaxReturn(array('code' => '403', 'msg' => '操作失败'));
    }

    // 获取订单信息
    public function getOrder(){
        $type = I('goodsType');
        $orderSn = I('orderSn');
        if(empty($type) || empty($orderSn)){
            $this->ajaxReturn(array('code' => '304', 'msg' => '参数错误'));
        }
        $tableName = $this->getTableName($type);
        $where = $this->getWhere($type,$orderSn);
        $joinStr = $this->getJoin($type);
        $Field = $this->getField($type);

        $data = M($tableName)
                ->field($Field)
                ->join($joinStr)
                ->where($where)
                ->select();
        if(empty($data)){
            $this->ajaxReturn(array('code' => '404', 'msg' => '无此数据'));
        }
        if($data[0]['payTime'] == null){
            $data[0]['payTime'] ='未付款';
        }
        $this->ajaxReturn(array('code' => '200', 'msg' => $data));
    }
    
    // 插询条件
    private function getField($type){
        switch($type){
            case 'tick':
                $Field ='a.t_order_sn orderSn,a.t_tick_name name,a.t_tick_create_time time,a.t_pay_time payTime,b.user_company company'; 
                break;
            case 'scenery':
                $Field ='a.o_order_sn orderSn,a.o_seceny_name name,a.o_order_time time,a.o_pay_time payTime,b.user_company company'; 
                break;
            case 'group':
                $Field ='a.g_order_sn orderSn,a.g_group_name name,a.g_order_time time,a.g_pay_time payTime,b.user_company company'; 
        }
        return $Field;
    }

    // 联表插询
    private function getJoin($type){
        switch($type){
            case 'tick':
                $JoinStr ='lf_user b on a.t_jsx_code=b.user_id'; 
                break;
            case 'scenery':
                $JoinStr ='lf_user b on a.o_jxs_code=b.user_id'; 
                break;
            case 'group':
                $JoinStr ='lf_user b on a.g_jxs_code=b.user_id'; 
        }
        return $JoinStr;
    }

    /**
     * 获取表名
     * @param string $type 类型识别值
     * @return string $tableName 表名
     */
    private function getTableName($type){
        switch($type){
            case 'tick':
                $tableName = 'tick_order a';
                break;
            case 'scenery':
                $tableName = 'seceny_order a';
                break;
            case 'group':
                $tableName = 'group_order a';
        }
        return $tableName;
    }

    /**
     * 获取删除条件
     * @param string $type 类型识别值
     * @return string $where 删除条件
     */
    private function getWhere($type,$code){
        switch($type){
            case 'tick':
                $where = array(
                    'a.t_order_sn'           =>   $code
                );
                break;
            case 'scenery':
                $where = array(
                    'a.o_order_sn'          =>     $code
                );
                break;
            case 'group':
                $where = array(
                    'a.g_order_sn'            =>   $code
                );
        }
        return $where;
    }
    
    // 海报 页面
    public function showPoster(){
        $count = M('img_table')->count("id");// 查询满足要求的总记录数
        $page = new \Org\Custom\Page($count,4);
        $show  = $page->show();// 分页显示输出
        $posterData = M("img_table")
                ->limit($page->firstRow.','.$page->listRows)
                ->select();
        $this->assign('page',$show);// 赋值分页输出
        $this->assign('poster',$posterData);                
        $this->display('home/showPoster');
    }

    // 订单查询
    public function showOrder(){
        $this->display('home/showOrder');
    }

    // 删除海报
    public function delPoster(){
        $code = I('posterId');
        if(empty($code)){
            $this->ajaxReturn(array('code' => '404', 'msg' => '参数错误'));
        }
        $res = M('img_table')->where('id='.$code)->select();
        $fileName = $res[0]['img_url'];
        $fileName = substr($fileName,0,strpos($fileName,'?'));
        $res = unlink($fileName);

        $res = M('img_table')->where('id='.$code)->delete();
        if($res){
            $this->ajaxReturn(array('code' => '200', 'msg' => '删除成功'));
        }
    }

    // 添加评论
    public function showComment(){
        $this->display('home/showComment');
    }

    // 修改销量
    public function showSales(){
        $this->display('home/showSales');
    }

    // 销量查询
    public function getGood(){
        $type = I('goodsType');
        $goodId = I('goodId');
        if(empty($type) || empty($goodId)){
            $this->ajaxReturn(array('code' => '304', 'msg' => '参数错误'));
        }
        switch($type){
            case 'tick':
                $sql = 'select t_code,t_tick_sell from lf_tick where t_code='.$goodId;
                $data = M()->query($sql);
                $newData['code']=$data[0]['t_code'];
                $newData['sell']=$data[0]['t_tick_sell'];
                break;
            case 'scenery':
                $sql = 'select s_code,s_sell from lf_scenery where s_code='.$goodId;
                $data = M()->query($sql);
                $newData['code']=$data[0]['s_code'];
                $newData['sell']=$data[0]['s_sell'];
                break;
            case 'group':
                $sql = 'select g_code,g_sell from lf_group where g_code='.$goodId;
                $data = M()->query($sql);
                $newData['code']=$data[0]['g_code'];
                $newData['sell']=$data[0]['g_sell'];
        }
        
        if(empty($newData)){
            $this->ajaxReturn(array('code' => '404', 'msg' => '无此数据'));
        }
        $this->ajaxReturn(array('code' => '200', 'msg' => $newData));
    }

    // 销量修改
    public function changeSales(){
        $type = I('goodsType');
        $goodId = I('goodId');
        $num = I('num');
        if(empty($type) || empty($goodId)){
            $this->ajaxReturn(array('code' => '304', 'msg' => '参数错误'));
        }
        switch($type){
            case 'tick':
                $sql = 'update lf_tick set t_tick_sell='.$num.' where t_code='.$goodId;
                break;
            case 'scenery':
                $sql = 'update lf_scenery set s_sell='.$num.' where s_code='.$goodId;
                break;
            case 'group':
                $sql = 'update lf_group set g_sell='.$num.' where g_code='.$goodId;
        }

        $res = M()->query($sql);
 
        $this->ajaxReturn(array('code' => '200'));
    }

    //上线
    public function goodsOnline(){
        $type = I("type");
        $code = I("code");
        if(empty($type) || empty($code)){
            $this->ajaxReturn(array('code' => '404', 'msg' => '参数错误'));
        }
        if($type == 1){ //跟团
            $this->groupOnline($code);
        }else if($type == 2){   //门票
            $this->ticketOnline($code);
        }else if($type == 3){   //景酒
            $this->sceneryOnline($code);
        }
    }

    //退款
    public function goodsRefund(){
        $type = I("type");
        $orderSn = I("orderSn");
        if(empty($type) || empty($orderSn)){
            $this->ajaxReturn(array('code' => '404', 'msg' => '参数错误'));
        }
        if($type == 1){ //跟团
            $this->groupRefund($orderSn);
        }else if($type == 2){   //门票
            $this->ticketRefund($orderSn);
        }else if($type == 3){   //景酒
            $this->sceneryRefund($orderSn);
        }
    }

    //跟团上线
    private function groupOnline($code){
        $where['g_code']        =  $code;
        $where['g_is_del']      =  array('neq', '1');
        $group = M("group")->field("g_id,g_is_pass")->where($where)->find();
        if(!$group){
            $this->ajaxReturn(array('code' => '403', 'msg' => '没有这个商品'));
        }
        if($group["g_is_pass"] == 5){
            $this->ajaxReturn(array('code' => '403', 'msg' => '这个商品已经在上线状态'));
        }
        $result = M('group')->where($where)->save(array("g_is_pass"=>5));
        if($result){
            $this->ajaxReturn(array('code' => '200', 'msg' => '上线成功'));
        }else{
            $this->ajaxReturn(array('code' => '405', 'msg' => '上线失败'));
        }
    }

    //门票上线
    private function ticketOnline($code){
        $where['t_code']         =  $code;
        $where['t_tick_del']     =  array('neq', '1');
        $tick = M("tick")->field("t_id,t_tick_type")->where($where)->find();
        if(!$tick){
            $this->ajaxReturn(array('code' => '403', 'msg' => '没有这个商品'));
        }
        if($tick["t_tick_type"] == 4){
            $this->ajaxReturn(array('code' => '403', 'msg' => '这个商品已经在上线状态'));
        }
        $result = M('tick')->where($where)->save(array("t_tick_type"=>4));
        if($result){
            $this->ajaxReturn(array('code' => '200', 'msg' => '上线成功'));
        }else{
            $this->ajaxReturn(array('code' => '405', 'msg' => '上线失败'));
        }
    }

    //景酒上线
    private function sceneryOnline($code){
        $where['s_code']         =  $code;
        $where['s_is_del']       =  array('neq', '1');
        $tick = M("scenery")->field("s_id,s_type")->where($where)->find();
        if(!$tick){
            $this->ajaxReturn(array('code' => '403', 'msg' => '没有这个商品'));
        }
        if($tick["s_type"] == 5){
            $this->ajaxReturn(array('code' => '403', 'msg' => '这个商品已经在上线状态'));
        }
        $result = M('scenery')->where($where)->save(array("s_type"=>5));
        if($result){
            $this->ajaxReturn(array('code' => '200', 'msg' => '上线成功'));
        }else{
            $this->ajaxReturn(array('code' => '405', 'msg' => '上线失败'));
        }
    }

    //跟团退款
    private function groupRefund($order_sn){
        $order = M("group_order")->field("g_order_id,g_order_type")->where(array("g_order_sn" => $order_sn))->find();
        if(!$order){
            $this->ajaxReturn(array('code' => '403', 'msg' => '没有这条订单'));
        }
        if($order["g_order_type"] == 6){
            $this->ajaxReturn(array('code' => '403', 'msg' => '这条订单已经是退款状态了'));
        }
        $result = M('group_order')->where(array("g_order_sn" => $order_sn))->save(array("g_order_type"=>"6"));
        if($result){
            $this->ajaxReturn(array('code' => '200', 'msg' => '退款状态改变成功'));
        }else{
            $this->ajaxReturn(array('code' => '405', 'msg' => '退款状态改变失败'));
        }
    }

    //门票退款
    private function ticketRefund($order_sn){
        $order = M("tick_order")->field("t_order_id,t_tick_order_type")->where(array("t_order_sn" => $order_sn))->find();
        if(!$order){
            $this->ajaxReturn(array('code' => '403', 'msg' => '没有这条订单'));
        }
        if($order["t_tick_order_type"] == 6){
            $this->ajaxReturn(array('code' => '403', 'msg' => '这条订单已经是退款状态了'));
        }
        $result = M('tick_order')->where(array("t_order_sn" => $order_sn))->save(array("t_tick_order_type"=>"6"));
        if($result){
            $this->ajaxReturn(array('code' => '200', 'msg' => '退款状态改变成功'));
        }else{
            $this->ajaxReturn(array('code' => '405', 'msg' => '退款状态改变失败'));
        }
    }

    //景酒退款
    private function sceneryRefund($order_sn){
        $order = M("seceny_order")->field("o_id,o_order_type")->where(array("o_order_sn" => $order_sn))->find();
        if(!$order){
            $this->ajaxReturn(array('code' => '403', 'msg' => '没有这条订单'));
        }
        if($order["o_order_type"] == 6){
            $this->ajaxReturn(array('code' => '403', 'msg' => '这条订单已经是退款状态了'));
        }
        $result = M('seceny_order')->where(array("o_order_sn" => $order_sn))->save(array("o_order_type"=>"6"));
        if($result){
            $this->ajaxReturn(array('code' => '200', 'msg' => '退款状态改变成功'));
        }else{
            $this->ajaxReturn(array('code' => '405', 'msg' => '退款状态改变失败'));
        }
    }


}
