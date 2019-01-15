<?php
/**********************************
* 项目:上传
* 文件: 10.210.210.147:/data1/t/shuchang1/develop/all.vic.sina.com.cn/common/work/common/s3/upload.php
* 功能: todo
* 日期: Sun Oct 09 07:25:20 GMT 201107:25:20
* 作者: shuchang1@staff.sina.com.cn
* 说明：
http://wiki.internal.sina.com.cn/moin//DAppCluster/DappClusterReadme2/DBA-Announce20110523

****************************************/
/*
ob_start();
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
//curl_setopt($ch, CURLOPT_URL, 'http://www.sina.com.cn');
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_NOBODY, 0);
$r = curl_exec($ch);
curl_close($ch);
$file_content = ob_get_contents();
ob_end_clean();
//$file_content=file_get_contents();
*/
#Sun Oct 09 07:31:47 GMT 201107:31:47 shuchang1 修改
#
if (!ONDEV) {
   // require("SinaService/SinaStorageService/SinaStorageService.php");
//    define("IMG_CACHE_DIR",$_SERVER[SINASRV_CACHE_DIR]);
//    define("IMG_CACHE_URL",$_SERVER[SINASRV_CACHE_URL]);
}

class s3Upload{
    public   $project = "http://platform.alltosun.net";
    public   $accesskey = "SINA00000000000SALES";
    public   $secretkey = "tyYXmhVGXmvJJJiwfeoqTOqCdKV/haHqrnwK0Pjy";
    public  $path;
    public  $filename;
    public  $expires=90;
    public $mime_types = array(
    'gif' => 'image/gif',
    'jpg' => 'image/jpeg',
    'jpeg' => 'image/jpeg',
    'jpe' => 'image/jpeg',
    'bmp' => 'image/bmp',
    'png' => 'image/png',
    'tif' => 'image/tiff',
    'tiff' => 'image/tiff',
    'pict' => 'image/x-pict',
    'pic' => 'image/x-pict',
    'pct' => 'image/x-pict',
    'tif' => 'image/tiff',
    'tiff' => 'image/tiff',
    'psd' => 'image/x-photoshop',

    'swf' => 'application/x-shockwave-flash',
    'js' => 'application/x-javascrīpt',
    'pdf' => 'application/pdf',
    'ps' => 'application/postscrīpt',
    'eps' => 'application/postscrīpt',
    'ai' => 'application/postscrīpt',
    'wmf' => 'application/x-msmetafile',

    'css' => 'text/css',
    'htm' => 'text/html',
    'html' => 'text/html',
    'txt' => 'text/plain',
    'xml' => 'text/xml',
    'wml' => 'text/wml',
    'wbmp' => 'image/vnd.wap.wbmp',

    'mid' => 'audio/midi',
    'wav' => 'audio/wav',
    'mp3' => 'audio/mpeg',
    'mp2' => 'audio/mpeg',

    'avi' => 'video/x-msvideo',
    'mpeg' => 'video/mpeg',
    'mpg' => 'video/mpeg',
    'qt' => 'video/quicktime',
    'mov' => 'video/quicktime',

    'lha' => 'application/x-lha',
    'lzh' => 'application/x-lha',
    'z' => 'application/x-compress',
    'gtar' => 'application/x-gtar',
    'gz' => 'application/x-gzip',
    'gzip' => 'application/x-gzip',
    'tgz' => 'application/x-gzip',
    'tar' => 'application/x-tar',
    'bz2' => 'application/bzip2',
    'zip' => 'application/zip',
    'arj' => 'application/x-arj',
    'rar' => 'application/x-rar-compressed',

    'hqx' => 'application/mac-binhex40',
    'sit' => 'application/x-stuffit',
    'bin' => 'application/x-macbinary',

    'uu' => 'text/x-uuencode',
    'uue' => 'text/x-uuencode',

    'latex'=> 'application/x-latex',
    'ltx' => 'application/x-latex',
    'tcl' => 'application/x-tcl',

    'pgp' => 'application/pgp',
    'asc' => 'application/pgp',
    'exe' => 'application/x-msdownload',
    'doc' => 'application/msword',
    'rtf' => 'application/rtf',
    'xls' => 'application/vnd.ms-excel',
    'ppt' => 'application/vnd.ms-powerpoint',
    'mdb' => 'application/x-msaccess',
    'wri' => 'application/x-mswrite',
    );
    function __construct()
    {
        $this->expires=$this->expires*24*60*60;
    }

    /**
     * Enter description here...
     *
     * @param unknown_type $path 文件夹
     * @param unknown_type $file_content 数据流
     * @param unknown_type $isSmall 是否要小图片
     * @param unknown_type $filetype 文件扩展名
     * @param unknown_type $filename 文件类型
     * @return unknown
     */
    public  function upload($path,$file_content,$isSmall=false,$w=300,$h=300,$filetype='jpg',$filename='',$prefix="thumb_")
    {
        if($path=='')$path=date("Ymd");
        $npath=$this->get_path(rand(1,1000000));
        //$npath=$this->get_path($path,rand(1,1000000));
        // 下面先这么改，因为地址中总出现 6-10.xls/7a/bb/6-10.xls
//         $this->path=$path."/".$npath;
         $this->path=$path;
        $filetype=strtolower($filetype);
        $mime_type=$this->mime_types[$filetype];
        if($filename=='')$filename=rand(10000,99999);
        $file_path=$this->path.$filename.".".$filetype;

        $file_length = strlen($file_content);
        //$file_sha1 = sha1($file_content);
        $o = SinaStorageService::getInstance($this->project, $this->accesskey, $this->secretkey);

        $o->setCURLOPTs(array(CURLOPT_VERBOSE=>1));
        $o->setQueryStrings(array("v"=>1));
        #Thu Feb 02 09:10:05 GMT 201209:10:06 shuchang1 修改
        #去掉过期时间
        $o->setExpires(time()+$this->expires);
        //是否验证
        $o->setAuth(true);
        $o->uploadFile($file_path,$file_content, $file_length, $mime_type, $result,true);
        //判断是否需要生成缩略图
        $pic=array();
        $pic['path']=$file_path;
        
            $pic['original']=$this->getfile($file_path);
            return $pic;
    }

    function upload_file($path,$file_content,$isSmall=false,$w=300,$h=300,$filetype='jpg',$filename='',$prefix="thumb_")
    {
        if($path=='')$path=date("Ymd");
        $npath=$this->get_path(rand(1,1000000));
        //$npath=$this->get_path($path,rand(1,1000000));
        $this->path=$path."/".$npath;
        $filetype=strtolower($filetype);
        $mime_type=$this->mime_types[$filetype];
        if($filename=='')$filename=rand(10000,99999);
        $file_path=$this->path.$filename.".".$filetype;

        $file_length = strlen($file_content);
        //$file_sha1 = sha1($file_content);
        $o = SinaStorageService::getInstance($this->project, $this->accesskey, $this->secretkey);

        $o->setCURLOPTs(array(CURLOPT_VERBOSE=>1));
        $o->setQueryStrings(array("v"=>1));
        #Thu Feb 02 09:10:05 GMT 201209:10:06 shuchang1 修改
        #去掉过期时间
        $o->setExpires(time()+$this->expires);
        //是否验证
        $o->setAuth(true);
        $o->uploadFile($file_path,$file_content, $file_length, $mime_type, $result,true);
        //判断是否需要生成缩略图
        $pic=array();
        $pic['path']=$file_path;
        if($isSmall===true)
        {
            $new_file=$this->resize_image($file_content,$w,$h);
            //file_get_contents($new_file);
            $new_file_path=$this->path.$prefix.$filename.".".$filetype;
            //$this->upload($new_file_path,file_get_contents($new_file),false);
            $o = SinaStorageService::getInstance($this->project, $this->accesskey, $this->secretkey);
            $o->setCURLOPTs(array(CURLOPT_VERBOSE=>1));
            $o->setQueryStrings(array("v"=>1));
            $o->setExpires(time()+$this->expires);
            //是否验证
            $o->setAuth(true);
            $c=file_get_contents($new_file);
            $o->uploadFile($new_file_path,$c, strlen($c), $mime_type, $result,true);
            $pic['small']=$this->getfile($new_file_path);
         }
            $pic['original']=$this->getfile($file_path);
            return $pic;

    }
    /**
     * 返回地址
     *
     * @param unknown_type $file_path
     * @return unknown
     */
    function getfile($file_path)
    {
        //获取文件
        $o = SinaStorageService::getInstance($this->project, $this->accesskey, $this->secretkey,true);

        $o->setCURLOPTs(array(CURLOPT_VERBOSE=>1));
        $o->setQueryStrings(array("v"=>1));
        $o->setExpires(time()+$this->expires);
        $o->setAuth(true);
        $o->getFileUrl($file_path,$result);
        return $result;
    }
    //删除文件
    function deletefile($file_path)
    {
        $o = SinaStorageService::getInstance($this->project, $this->accesskey, $this->secretkey,true);
        $o->setCURLOPTs(array(CURLOPT_VERBOSE=>1));
        $o->setQueryStrings(array("v"=>1));
        $o->setExpires(time()+$this->expires);
        $o->setAuth(true);
        $o->deleteFile($file_path,$result);
        return $result;
    }
    /**
 * 创建目录
 *
 * @param $base_dir:目录基路径
 * @param $key:关键值
 * @param $series:目录级数,1级或2级，默认为2级
 *
 * @return 两目录名，不包括根目录
 */
function get_path( $key, $series=2)
{
    $m = strtolower(md5($key));
    if ($series == 1)
    {
        $d = $m{0}.$m{3};
    }
    else
    {
        $d = $m{0}.$m{3}."/".$m{1}.$m{2};
    }
    return $d."/";
}//end funtion

/***********************************************************
    * 本函数从源文件取出图象，成比例缩小，输出指定长宽的图片到目的文件
    * 如果原图尺寸比指定尺寸小，周围补充背景；
    * 源文件格式：gif,jpg,png
    * 目的文件格式：同源文件格式
    * $srcFile: 源文件 (*.jpg/*.gif/*.png)
    * $dstDir: 目标文件目录(相对于域名存储目录的相对目录)
    * $dstFilename: 目标文件名 (一定不能带扩展名,自动添加同源文件相同的扩展名)
    * $Width: 目标图片宽度
    * $Height: 目标图片高度
    * $bgcolor: 缩放后图片填充背景颜色
    * $watermark: 水印图片文件名，为空表示不添加水印
    * $position: 水印图片位置（1=左上角，2=右上角，3=左下角,4=右下角）
    * $padding: 水印图片距边缘距离
    * 返回目标文件信息数组
    * return array("filename"=>图片路径和名称,"dirname"=>图片目录,"basename"=>图片名(包括扩展名),"extension"=>图片扩展名);。

************************************************************/

function resize_image($srcFile,$Width,$Height,$bgcolor="#FFFFFF",$watermark="",$position=4,$padding=20)
{
        if(!is_dir(IMG_CACHE_DIR.IMG_DIR))
    {
        @mkdir(IMG_CACHE_DIR.IMG_DIR);
    }

    if($srcFile=='')
    {
        //echo 'ttt';
        return '';
    }
    $filename = date("YmdHis").rand(0,99).'.jpg';
$file = fopen(IMG_CACHE_DIR.IMG_DIR.$filename, 'w');
if(!fwrite($file, $srcFile)){
    //echo 'Error writing to file';
    //echo 'ttt2';
    return '';
}
fclose($file);
//echo IMG_CACHE_URL.IMG_DIR.$filename;
$srcFile=IMG_CACHE_DIR.IMG_DIR.$filename;

    $return_info = array();
    $mymagickwand = NewMagickWand();
    if (!MagickReadImage($mymagickwand, $srcFile))
    {
        //echo 'ttt3';
        return $return_info;
    }

    //尺寸
    $srcW = MagickGetImageWidth($mymagickwand);
  $srcH = MagickGetImageHeight($mymagickwand);
    $srcR = $srcW/$srcH;
    $dstR = $Width/$Height;
    $newW = 0; //新图片高度
    $newH = 0; //新图片宽度
    if($Width > $srcW)
    {
        if ($Height > $srcH)
        {
            $newW = $srcW;
            $newH = $srcH;
        }
        else
        {
            $newH = $Height;
            $newW = round($newH*$srcR);
        }
    }
    else
    {
        if ($dstR > $srcR)
        {
            $newH = $Height;
            $newW = round($newH*$srcR);
        }
        else
        {
            $newW = $Width;
            $newH = round($newW/$srcR);
        }
    }
    $newL = round(($Width-$newW)/2); //新图片左边距
    $newT = round(($Height-$newH)/2); //新图片顶边距
    if ($newL < 0)
    {
        $newL = 0;
    }
    if ($newT < 0)
    {
        $newT = 0;
    }

    //类型
    $srcT = MagickGetImageFormat($mymagickwand);
    if ($srcT == "JPEG")
    {
        $extension = "jpg";
    }
    elseif ($srcT == "GIF")
    {
        $extension = "gif";
    }
    elseif ($srcT == "PNG")
    {
        $extension = "png";
    }
    else
    {
        return $return_info;
    }

    //建立临时文件
    $tmp_f = tempnam($_SERVER["SINASRV_CACHE_DIR"],"TMP_IMG");

    //生成背景图
    $bgmagickwand = NewMagickWand();
    MagickNewImage($bgmagickwand,$Width,$Height,$bgcolor);
    MagickSetFormat($bgmagickwand,$srcT);

    //缩放原图并合并到背景图上
    MagickScaleImage($mymagickwand, $newW, $newH);
    MagickCompositeImage($bgmagickwand,$mymagickwand,MW_OverCompositeOp,$newL,$newT);

    //处理水印图
    if ($watermark && is_file($watermark))
    {
        MagickRemoveImage($mymagickwand);
        $padding = intval($padding);
        if (MagickReadImage($mymagickwand, $watermark))
        {
            if ($position == 1)
            {
                $wmL = $padding;
                $wmT = $padding;
            }
            elseif ($position == 2)
            {
                $wmL = $Width-$padding-MagickGetImageWidth($mymagickwand);
                $wmT = $padding;
            }
            elseif ($position == 3)
            {
                $wmL = $padding;
                $wmT = $Height-$padding-MagickGetImageHeight($mymagickwand);
            }
            else
            {
                $wmL = $Width-$padding-MagickGetImageWidth($mymagickwand);
                $wmT = $Height-$padding-MagickGetImageHeight($mymagickwand);
            }
            MagickCompositeImage($bgmagickwand,$mymagickwand,MW_OverCompositeOp,$wmL,$wmT);
        }
    }

    MagickWriteImage($bgmagickwand, $tmp_f);
    DestroyMagickWand($mymagickwand);
    DestroyMagickWand($bgmagickwand);

    return $tmp_f;
}
}

//demo
/*
if($_GET['test']==1)
{
$s3Upload= new  s3Upload();
$info=$s3Upload->upload("test",file_get_contents('http://cs.sina.com.cn/minisite/2011/201110colgate/images/team_hx_01.jpg'),false);
print_r($info);
}
*/
?>