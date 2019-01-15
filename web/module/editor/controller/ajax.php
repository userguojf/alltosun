<?php
/**
 * alltosun.com  ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2016-11-15 上午11:40:05 $
 * $Id$
 */

class Action
{
    public function upload_img()
    {
        // 执行上传文件
        $cover = false;

        $files_info = $_FILES['imgData'];

        $state = '';
        $title = '';

        // p($files_info);
        if ($_FILES['imgData']['tmp_name']) {

            $cover = upload_file($_FILES['imgData'], false, 'coupon_content');

            $photo_path = _image($cover, 'big', 'coupon_content');

            $title  = htmlspecialchars($files_info['name'], ENT_NOQUOTES);

            $state = 'SUCCESS';
        }

        exit(json_encode(array('url'=>$photo_path, 'title'=>$title, 'state'=>$state)));
    }
    
    public function upload_file()
    {
        // 执行上传文件
        $cover = false;

        $files_info = $_FILES['fileData'];

        $state = '';
        $title = '';

        // p($files_info);
        if ($_FILES['fileData']['tmp_name']) {

            $path = deploy_helper::upload_file_self($_FILES['fileData'] , false , 'guojf');
//         upload_file();
// p($path);exit();

            $title  = htmlspecialchars($files_info['name'], ENT_NOQUOTES);

            $state = 'SUCCESS';
        }

        exit(json_encode(array('url'=> UPLOAD_URL.$path, 'title'=>$title, 'state' => $state)));
    }
    
    
}