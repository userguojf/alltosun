<?php
/**
  * alltosun.com 视频widget video.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年7月3日 下午4:19:57 $
  * $Id$
  */
class video_widget
{
    //设置文件大小不超过50MB
    public static $max_size_file_upload =  '50000000';

    //允许上传的文件扩展名
    public $array_extention_interdite = array( '.flv' , '.wmv' , '.rmvb' , '.mp4');

    //上传路径
    public static $upload_dir = '';

    public function __construct()
    {
        self::$upload_dir = UPLOAD_PATH.'/video';
    }

    public function upload_video($name)
    {
        $file_info = isset($_FILES[$name]) ? $_FILES[$name] : array();

        //验证上传
        $result = $this->check_upload($file_info);

        if (!empty($result)) {
            return array('errno' => 1001, 'msg' => $result);
        }

        $video_name = $this->build_video_name('mp4');

        if (move_uploaded_file( $file_info['tmp_name'], $video_name)){
            /*
             * 视频压缩  暂时不部署到线上
             *   //获取后缀
             *   $ext = pathinfo($video_name, PATHINFO_EXTENSION);
             *   //新的文件名称
             *   $to_video_name = str_replace('.'.$ext, '_compress.'.$ext, $video_name);
             *   $to_video_name = $this->compress_video($video_name,  $to_video_name);
             */

            $to_video_name = $video_name;
            return array('errno' => 0,  'file' => str_replace(self::$upload_dir, '', $to_video_name));
        }

        return array('errno' => 1001,  'msg' => '文件上传失败');
    }

    /**
     * 生成新的视频文件名称
     * @param unknown $ext 后缀
     */
    public function build_video_name($ext)
    {
        $time = time();
        $folder = self::$upload_dir.date('/Y/m/d', $time);
        if (!file_exists($folder) && !@mkdir($folder, 0777, true)) {
            return false;
        }

        //按当前时间+随机数（用于区分同一秒多个进程上传不同文件）存放文件
        static $count = 1;

        // u为microseconds，> PHP 5.2.2
        if (version_compare(PHP_VERSION, '5.2.2') >= 0) {
            $current_time = date('YmdHisu', $time);
        } else {
            $current_time = date('YmdHis', $time);
        }

        $random = mt_rand(0, 100);

        $target = $folder.'/'.$current_time.'_'.$count.'_'.$random.'.'.$ext;

        $count++;

        if (file_exists($target)) {
            return $this->get_video_name($ext);
        }

        return $target;
    }

    /**
     * 验证文件上传状态
     * @param array $file_info 上传的文件信息，$_FILES['name']
     * @return string 错误信息failed_msg
     * @author gaojj@alltosun.com
     */
    function check_upload($file_info = null, $max_size = 0)
    {
        if (!isset($file_info)) {
            return '没有找到上传的文件';
        }
        if (isset($file_info['error']) && $file_info['error'] != 0) {
            $error_mapping = array(
                    0   =>  "上传成功！",
                    1   =>  "服务器限制的上传文件大小为".ini_get('upload_max_filesize'),
                    2   =>  "上传文件大小超过了表单中MAX_FILE_SIZE的限制！",
                    3   =>  "只有部分文件被上传了，请重试！",
                    4   =>  "没有选择要上传的文件。",
                    6   =>  "服务器上传临时目录不存在，请联系系统管理员。",
                    7   =>  "文件无法写入磁盘，请联系系统管理员。",
                    8   =>  "某个PHP扩展导致上传失败，请联系系统管理员。"
            );

            $error_id = $file_info['error'];

            return '上传失败，'.$error_mapping[$error_id];
        }

        if (empty($file_info['size'])) {
            return '选中的文件大小为空';
        }
        $image_max_size = $max_size ? $max_size : self::$max_size_file_upload;
        $file_extension_name = pathinfo($file_info['name'], PATHINFO_EXTENSION);
        if ($image_max_size && $file_info['size'] > $image_max_size) {
            return '上传文件的大小不得超过'.conversion($image_max_size);
        }

        if (empty($file_info['name'])) {
            return '选中的文件没有文件名';
        }

        if (empty($file_info['tmp_name'])) {
            return '上传到服务器临时目录失败';
        }

        $postfix = substr($file_info['name'], strrpos($file_info['name'], '.'));
        if (!in_array($postfix, $this->array_extention_interdite)) {
            return '不支持的视频格式';
        }


        return '';
    }

    /**
     * 获取指定路径视频
     * @param string $path 视频路径
     * @return string 图片的完整路径
     * @author wangjf@alltosun.com
     */
    public function _video($path, $all=0)
    {
        if (!$path) return '';

        if (strncasecmp($path, 'http://', 7) == 0) {
            // 注：已http://开头的图片都直接返回
            return $path;
        }

        if (substr($path, 0, strlen(self::$upload_dir)) != self::$upload_dir){
            $path = 'upload/video'.$path;
        }

        if (!file_exists(ROOT_PATH.'/'.$path)){
            return '';
        }

        if ($all) {
            $path = STATIC_URL.'/'.$path;
        }
        return $path;
    }

    /**
     * 视频压缩
     * @param unknown $video_path
     * @param number $scale 压缩阀值
     * @param unknown $to_video_path
     */
    public function compress_video($video_path, $to_video_path, $crf=0)
    {
        if (!$crf) {
            $crf = screen_content_new_config::$ffmpeg_crf;
        }

        $result = array();

        $error = exec("/usr/local/bin/ffmpeg -y -i {$video_path} -vcodec libx264 -crf {$crf} -c:a copy {$to_video_path} 2>&1", $result);

        if (file_exists($to_video_path)) {
            //删除原有
            unlink($video_path);
            return $to_video_path;
        } else {
            return $video_path;
        }
    }
}