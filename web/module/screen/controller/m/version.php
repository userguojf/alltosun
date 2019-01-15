<?php

/**
 * alltosun.com  version.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月6日 下午5:05:14 $
 * $Id$
 */

class Action
{
    public function index()
    {
        $type = Request::get('source','default');
        $this->add_record($type, 1);
        Response::assign('type', $type);
        Response::display('m/down.html');

    }
    
    public function down_load()
    {
        $source = Request::get('source','default');

        $info = _model('screen_version')->read(array('status'=>2), ' ORDER BY `id` DESC ');
        if(!$info) {
            return "当前无正式版本！";
        }
        $this->add_record($source, 2);

        _model('screen_version')->update($info['id'], array('down_num'=>$info['down_num']+1));
        
        $url = SITE_URL.$info['path'];

//         $showname = "甩手掌柜.apk";

//         header('Content-Description: File Transfer');
//         header('Content-Type: application/octet-stream');
//         header("Accept-Ranges: bytes");
//         header("Accept-Encoding: deflate");
//         header('Content-Disposition: attachment; filename="'.$showname.'"');
//         header('Expires: 0');
//         header('Cache-Control: must-revalidate');
//         header('Pragma: public');
//         header('Content-Length: ' . filesize($url));

//         readfile($url);
//         exit;
            Response::redirect($url);
            Response::flush();
    }

    public function add_record($source, $type)
    {
        $info = [
            'source' => $source,
            'type' => $type
        ];
        _model('screen_version_record')->create($info);

    }
}