<?php

/**
 * alltosun.com sina图片上传处理 file_upload.php
 * ============================================================================
 * 版权所有 (C) 2007-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2011-5-29 上午02:26:54 $
*/

class Action
{
    // 缩略图宽度
    private $width  = 144;
    // 缩略图高度
    private $height = 144;

    public function __call($action = '', $params = array())
    {
        header("Access-Control-Allow-Origin: http://app.alltosun.com");
        header("Access-Control-Allow-Methods: POST");

        $user_id = user_helper::get_user_id();
        if (!$user_id) {
            $this->output('请先登录再进行操作');
            exit();
        }

//        $result = check_weibo_account();
//        if (true !== $result) {
//            $this->output($result);
//            exit();
//        }

        // 图片上传压缩
        $allow_type  = array_merge(Config::get('allow_image_type'));
        $upload_path = UPLOAD_PATH;

        // 上传验证
        $failed_msg = check_upload($_FILES['image_upload'], 500*1024);
        if (!empty($failed_msg)) {
            $this->output($failed_msg);
            exit();
        }

        // 上传
        $uploadr = new Uploadr($upload_path, $allow_type);
        try {
            $file_path = $uploadr->uploadFile($_FILES['image_upload']['tmp_name']);
        } catch (Exception $e) {
            $this->output($e->getMessage());
            exit();
        }

        $res_name = 't';
        make_thumb($file_path, $res_name);
        $thumb = _image($file_path, 'small');

        $this->output(array('info'=>'ok', 'thumb'=>$thumb, 'path'=>$file_path, 'file_name'=>$_FILES['image_upload']['name']));
        exit();
    }

    private function output($msg)
    {
        if (is_string($msg)) {
            $msg = array('info'=>$msg);
        }
        echo json_encode($msg);
        exit();
    }
}
?>