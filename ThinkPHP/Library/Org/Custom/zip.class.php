<?php
namespace Org\Custom;
require "ThinkPHP/Library/Org/Custom/download.php";
class zip{
    public $currentdir;//当前目录
    public $filename;//文件名
    public $fileinfo;//用于保存当前目录下的所有文件名和目录名以及文件大小
    public $title;
    public function __construct($name,$title){
        $this->currentdir=getcwd().DIRECTORY_SEPARATOR.$name;//返回当前目录
        $this->title=$title;
    }
    //遍历目录 todo img分文件夹 递归
    public function scandir($filepath){
//        echo $filepath;
        if (is_dir($filepath)){
            $arr=scandir($filepath);
            foreach ($arr as $k=>$v){
                $this->fileinfo[$v][]=$this->getfilesize($v);
            }
//                var_dump($this->fileinfo);
        }else {
            echo "<script>alert('当前目录不是有效目录');</script>";
        }
    }
    /**
     * 返回文件的大小
     *
     * @param string $filename 文件名
     * @return 文件大小(KB)
     */
    public function getfilesize($fname){
        return filesize($fname)/1024;
    }

    /**
     * 压缩文件(zip格式)
     */
    public function tozip(){
        $zip=new \ZipArchive();
        $zipname=$this->currentdir.DIRECTORY_SEPARATOR.date('YmdHis',time());
        if (!file_exists($zipname)){
            $opened=$zip->open($zipname.'.zip',\ZipArchive::CREATE);//创建一个空的zip文件
            if( $opened !== true ){
               die("zip文件夹创建失败！");
            }
            $items=$this->fileinfo;
            $items=array_keys($items);
            for ($i=0;$i<count($items);$i++){
                if($items[$i] != "." && $items[$i] != ".."){
                    if(is_dir($this->currentdir.DIRECTORY_SEPARATOR.$items[$i])){
                        $zip->addFile($this->currentdir.DIRECTORY_SEPARATOR.$items[$i],$items[$i]);
                    }
                }
            }
            $zip->close();
//            var_dump($this->fileinfo);
            $dw=new download($zipname.'.zip',$this->title); //下载文件
            $dw->getfiles();
            unlink($zipname.'.zip'); //下载完成后要进行删除
        }
    }

    public function more_tozip(){
        $zip = new \ZipArchive();
//参数1:zip保存路径，参数2：ZIPARCHIVE::CREATE没有即是创建
        $zipname=$this->currentdir;
        if(!$zip->open("$zipname.zip",\ZIPARCHIVE::CREATE))
        {
            echo "创建[$zipname.zip]失败<br/>";return;
        }
//echo "创建[$exportPath.zip]成功<br/>";
        $this->createZip(opendir($zipname),$zip,$zipname);
        $zip->close();
        $dw=new download($zipname.'.zip',$this->title); //下载文件
        $dw->getfiles();
        unlink($zipname.'.zip'); //下载完成后要进行删除
    }


/*压缩多级目录
    $openFile:目录句柄
    $zipObj:Zip对象
    $sourceAbso:源文件夹路径
*/
    function createZip($openFile,$zipObj,$sourceAbso,$newRelat = '')
    {
        while(($file = readdir($openFile)) != false)
        {
            if($file=="." || $file=="..")
                continue;

            /*源目录路径(绝对路径)*/
            $sourceTemp = $sourceAbso.DIRECTORY_SEPARATOR.$file;
            /*目标目录路径(相对路径)*/
            $newTemp = $newRelat==''?$file:$newRelat.DIRECTORY_SEPARATOR.$file;
            if(is_dir($sourceTemp))
            {
                //echo '创建'.$newTemp.'文件夹<br/>';
                $zipObj->addEmptyDir($newTemp);/*这里注意：php只需传递一个文件夹名称路径即可*/
                $this->createZip(opendir($sourceTemp),$zipObj,$sourceTemp,$newTemp);
            }
            if(is_file($sourceTemp))
            {
                //echo '创建'.$newTemp.'文件<br/>';
                $zipObj->addFile($sourceTemp,$newTemp);
            }
        }
    }
}
?>
