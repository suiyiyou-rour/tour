<?php

class newImage
{

    /**
     * 图片合成 左上方
     * @param string $bigImagePath   大图地址
     * @param string $qrCodePath     小图地址 
     * @param string $savePath       保存地址
     * @param int    $left           距离左边的位置
     * @param int    $top            距离上方的位置
     */

    public function qCode($bigImagePath,$qrCodePath,$savePath,$left=0,$top=0){
        if(!isset($bigImagePath) || !isset($qrCodePath) || !isset($savePath)) return '参数错误';
        
        // 获取并创建图片资源
        $bigImg = imagecreatefromstring(file_get_contents($bigImagePath));
        $qrCodeImg = imagecreatefromstring(file_get_contents($qrCodePath));
        
        // 获取图片参数
        list($qCodeWidth, $qCodeHight, $qCodeType) = getimagesize($qrCodePath);
        list($bigWidth, $bigHight, $bigType) = getimagesize($bigImagePath);

        // 图片合成
        imagecopymerge($bigImg, $qrCodeImg,$left,$top, 0, 0, $qCodeWidth, $qCodeHight, 100);

        // 图片生成
        header('content-type:image/jpeg');
        $res = imagejpeg($bigImg,$savePath);

        // 图片资源销毁
        imagedestroy($bigImg); 
        imagedestroy($qrCodeImg); 
    }
    /**
    * 图片合成 右下方
    * @param string $bigImagePath   大图地址
    * @param string $qrCodePath     小图地址 
    * @param string $savePath       保存地址
    * @param int    $right          距离右边的位置
    * @param int    $bottom         距离下方的位置
    */
    public function lowerRight($bigImagePath,$qrCodePath,$savePath,$right=0,$bottom=0){
        if(!isset($bigImagePath) || !isset($qrCodePath) || !isset($savePath)) return '参数错误';
        
        // 获取并创建图片资源
        $bigImg = imagecreatefromstring(file_get_contents($bigImagePath));
        $qrCodeImg = imagecreatefromstring(file_get_contents($qrCodePath));
        
        // 获取图片参数
        list($qCodeWidth, $qCodeHight, $qCodeType) = getimagesize($qrCodePath);
        list($bigWidth, $bigHight, $bigType) = getimagesize($bigImagePath);

        // 图片合成
        imagecopymerge($bigImg, $qrCodeImg,$bigWidth-$qCodeWidth-$right,$bigHight-$qCodeHight-$bottom, 0, 0, $qCodeWidth, $qCodeHight, 100);

        // 图片生成
        header('content-type:image/jpeg');
        $res = imagejpeg($bigImg,$savePath);

        // 图片资源销毁
        imagedestroy($bigImg); 
        imagedestroy($qrCodeImg); 
    }

    /**
     * 缩略图
     * @param string $imagePath      图片地址
     * @param string $savePath       保存地址
     * @param int    $width          缩略宽度
     * @param int    $hight          缩略长度
     */
    public function thumb($imagePath,$savePath,$width=0,$hight=0){
        
        if(!isset($imagePath) || !isset($savePath) || !isset($width) || !isset($hight)) return '参数错误';
        
        // 获取并创建图片资源
        $src = imagecreatefromstring(file_get_contents($imagePath));
        $des = imagecreate($width,$hight);
        
        // 图片填充
        imagecopyresampled($des, $src, 0, 0, 0, 0, $width, $hight, imagesx($src), imagesy($src));
        
        // 图片生成
        $res=imagepng($des,$savePath);

        // 图片资源销毁
        imagedestroy($des); 
        imagedestroy($src); 
    }

    


}