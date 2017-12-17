<?php

namespace Page\Controller;
use Think\Controller;
class UserLoginController extends Controller
{
    /**
     * 游客登陆
     */
    public function login_check()
    {
        $userName = I('post.name');
        $userPwd = md5(I('post.pwd'));
        $result = M('user')->where(array('user_account' => $userName, 'user_pwd' => $userPwd))->find();
        if (!$result) {
            $this->ajaxReturn(array('code' => '404', 'msg' => '账号或密码输出错误！'));
        }
        $data['code'] = $userName;
        $data['type'] = $result['user_type'];
        session('user_online',$data);
        $this->ajaxReturn(array('code' => '200', 'msg' => '登陆成功', 'type' => $result['user_type'], 'userCode' => $result['user_id']));//1 游客 2 经销商
    }

    /**
     * 游客注册
     */
    public function userRegister()
    {
        $code = I('post.code');
        $mobile = I('post.mobile');
        $pwd = md5(I('post.pwd'));
        $repwd = md5(I('post.repwd'));
        // 手机
        if (!preg_match('/^1\d{10}$/',$mobile)){
            $this->ajaxReturn(array('code' => 404, 'msg' => '手机号码错误'));
        }
        // 密码校验
        if ($pwd != $repwd){
            $this->ajaxReturn(array('code' => 404, 'msg' => '两次密码不相等'));
        }
        // 验证码
        $result = M('sms_code')->where(array('c_code' => $code, 'c_mobile' => $mobile,'c_type' => array('elt',4)))->find();
        if (empty($result)) {
            $this->ajaxReturn(array('code' => 404, 'msg' => '验证码错误'));
        }
        // 注册失败
        $result = M('user')->add(array('user_account' => $mobile, 'user_mobile' => $mobile, 'user_pwd' => $pwd, 'user_regist_time' => time(), 'user_type' => 1));
        if (!$result) {
            $this->ajaxReturn(array('code' => 404, 'msg' => '注册失败'));
        }
        
        $data['code'] = $mobile;
        $data['type'] = 1;
        session('user_online',$data);
        $this->ajaxReturn(array('code' => 200, 'msg' => '注册成功'));
    }

    /**
     * 经销商注册
     */
    public function jxsRegister()
    {
        $lxmobile = session('user_online')['code'];  // 联系人电话
        if(!$lxmobile){
            $this->ajaxReturn(array('code' => 401,'msg' => session('user_online')));
        }
        $company = I('post.company');  // 公司名
        $name = I('post.name');  //  联系人名称
        $email = I('post.email'); // 邮箱
        $province = '福建'; // 省份
        $city = I('post.city'); // 城市
        $fax = I('post.fax');  // 传真

        if($company && $name && $email && $city && $fax){
            $imgArr = $this->image_upload($_FILES['file']);
            if($imgArr['code'] == 0){
                $this->ajaxReturn(array('code' => 405,'msg' => '营业执照上传失败！'));
            }
            $data['user_js_file'] = $imgArr['message']; // 营业执照路径
            $data['user_account'] = $lxmobile;  // 联系人code
            $data['user_type'] = 3;     // 审批状态 1 游客 2 经销商 3 审核
            $data['user_mobile'] = $lxmobile; 
            $data['user_email'] = $email;
            $data['user_company'] = $company;
            $data['user_province'] = $province;
            $data['user_city'] = $city;
            $data['user_fax'] = $fax;
            $data['user_lx_mobile'] = $lxmobile;
            $data['user_lx_phone'] = $lxmobile;
        
            $result = M('user')->where('user_account ='.$lxmobile)->save($data);
            if ($result) {
                $this->ajaxReturn(array('code' => 200 ,'msg' => '已提交审核！'));
            } else {
                $this->ajaxReturn(array('code' => 404,'msg' => '注册失败'));
            }

        }else{
            $this->ajaxReturn(array('code' => 402,'msg' => '请认真填写数据'));
        }
    }
    /**
     * 图片上传
     * 
     */
    public function image_upload($image){//图片上传
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728;// 设置附件上传大小
        $upload->exts = array('jpg', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = './Public/Page/license/'; // 设置附件上传根目录
        $upload->saveName = array('uniqid', '');// 采用唯一命名
        $upload->savePath = ''; // 设置附件上传（子）目录
        $upload->autoSub = true;//自动使用子目录保存上传文件 默认为true
        $upload->subName = array('date', 'Ymd');

        $info = $upload->uploadOne($image);
        if (!$info) {// 上传错误提示错误信息
            return array("code"=>0,"message"=>$upload->getError());
        } else {// 上传成功 获取上传文件信息
            $image_k="";
            //取出数组里的名字和文件夹
            $image_k .= "./Public/Page/license/".$info["savepath"] . $info["savename"];
            return array("code"=>1,"message"=>$image_k);
        }
    }

    /**
     *  PC端重名验证
     *  @param $mobile 
     *  @return code 200  可以注册   404 用户已存在
     */
     public function isRegister()
     {  
        $mobile = I('post.mobile'); 
        $code   = 200;
        $res = M('user')->field('user_account')->where('user_account ='.$mobile)->find();
        if($res){
            $code = 404;
        }
        $this->ajaxReturn(array('code' => $code));   
     }
}