<?php
/**
 * Created by zhang.
 * User: zhang
 * Date: 2017/11/11
 * Time: 11:54
 */

namespace Home\Controller;


class GysController extends BaseController
{

    public function getInfo()
    {
        $info = M('sp')->where(array('sp_id' => $this->userId))->find();
        $info['sp_file'] = json_decode($info['sp_file'], true);
        foreach ($info['sp_file'] as &$f) {
            $f = C('img_url') . $f;
        }
        if (empty($info['sp_com_name'])) {
            $flag = 1;
        } else {
            $flag = 2;
        }
        $return['flag'] = $flag;
        $return['info'] = $info;
        $this->ajaxReturn($return);
    }

    public function saveInfo()
    {
        $info = M('sp')->where(array('sp_id' => $this->userId))->find();
        if (empty($info['sp_com_name'])) {
            $data['sp_com_name'] = I('post.cname');
            $data['sp_mobile'] = I('post.mobile');
            $data['sp_name'] = I('post.name');
            $data['sp_address'] = I('post.address');
            $file = I('post.img');
            $path = "./Public/gys/";
            foreach ($file as $k => $t) {
                $ifl = $this->addr($t, $path, $k);
                $cc[] = $ifl;
            }
            $data['sp_file'] = json_encode($cc);
        } else {
            $data['sp_mobile'] = I('post.mobile');
            $data['sp_name'] = I('post.name');
        }

        M('sp')->where(array('sp_id' => $this->userId))->save($data);
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

}