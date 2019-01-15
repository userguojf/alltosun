<?php

/**
 * alltosun.com 文件上传类
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2009-11-04 17:02:05 +0800 $
 * $Id: Uploadr.php 440 2012-08-16 00:11:00Z qianym $
*/

/**
 * 文件扩展名不等于文件类型
 * @link http://www.getid3.org/demo/
 */
// $up = new Uploadr('./files', array('jpg','gif','png'), 1024);
// $up->uploadFile('./3.jpg');

class Uploadr
{
    private $upload_dir = null;
    private $allow_type = null; // 当allow_type为null时允许上传任意类型的文件，为array()时为不允许上传文件
    private $allow_size = null;
    private $image_type = array('jpg', 'jpeg', 'gif', 'png'); // 图片类型

    /**
     * 上传初始化
     * @param $upload_dir str 上传文件的路径
     * @param $allow_type mixed 当allow_type为null时允许上传任意类型的文件，为array()时为不允许上传文件
     * @param $allow_size int 限制的文件大小
     * @return
     */
    public function __construct($upload_dir = '.', $allow_type = array('jpg'), $allow_size = 999999999)
    {
        $this->upload_dir = $upload_dir;
        $this->allow_type = $allow_type;
        $this->allow_size = $allow_size;
    }

    private function getTarget($file, $ext)
    {
        $time = time();
        $folder = $this->upload_dir.date('/Y/m/d', $time);
        if (!file_exists($folder) && !@mkdir($folder, 0777, true)) {
            return false;
        }

        // 如果是图片的话，则按当前时间+文件大小+随机数（用于区分同一秒多个进程上传不同文件）存放文件
        // 这样可以让不同模型使用同一张图片可以生成不同缩略图
        if (in_array($ext, $this->image_type)) {
            static $count = 1;

            // u为microseconds，> PHP 5.2.2
            if (version_compare(PHP_VERSION, '5.2.2') >= 0) {
                $current_time = date('YmdHisu', $time);
            } else {
                $current_time = date('YmdHis', $time);
            }

            $random = mt_rand(0, 100);

            $target = $folder.'/'.$current_time.'_'.$count.'_'.filesize($file).'_'.$random.'.'.$ext;

            $count++;
        } else {
            // 同一文件只上传一次
            $target = $folder.'/'.md5_file($file).filesize($file).'.'.$ext;
        }

        if (!file_exists($target)) {
            if (!rename($file, $target)) {
                return false;
            }
        }

        return substr($target, strlen($this->upload_dir));
    }

    private function analyzeFile($file)
    {
        try {
            $analyze = new getid3();
            $analyze = $analyze->analyze($file);
        } catch (Exception $e) {
            return false;
        }

        //当allow_type为null时允许上传任意类型的文件，为array()时为不允许上传文件
        if (($this->allow_type!==null) && !in_array($analyze['fileformat'], $this->allow_type)) {
            return false;
        }
        return $analyze['fileformat'];
    }

    public function uploadFile($file)
    {
        // #13795 防止nfs网络通信超时，导致重新创建upload目录
        if (!file_exists($this->upload_dir)) {
            throw new Exception('UPLOAD DIR IS NOT EXIST', 0);
        }

        if (filesize($file) >= $this->allow_size) {
            throw new Exception("FILE_SIZE IS TOO LARGE", 1);
        }
        if (!$ext = $this->analyzeFile($file)) {
            throw new Exception("NOT ALLOW FILE_TYPE", 2);
        }
        return $this->getTarget($file, $ext);
    }

    public function uploadData($data)
    {
        $temp = tempnam(sys_get_temp_dir(), "FOO");
        file_put_contents($temp, $data);

        return $this->uploadFile($temp);
    }
}
?>