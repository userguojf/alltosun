<?php
/**
 * alltosun.com  screen_version.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 黄迎秋 (huangyq@alltosun.com) $
 * $Date: 2017年7月3日 下午2:24:14 $
 * $Id$
 */

class Action
{
    private $per_page = 20;


    /**
     * 版本列表
     */
    public function index()
    {
        $page_no        = Request::getParam('page_no', 1);
        $search_filter  = Request::getParam('search_filter', array());
        $status         = Request::getParam('status', 1);

        $filter = array();
        if (isset($search_filter['type']) && $search_filter['type']) {
            $filter['type'] = $search_filter['type'];
        }
        if (isset($search_filter['start_date']) && $search_filter['start_date']) {
            $filter['add_time >='] = $search_filter['start_date'].' 00:00:00';
        }
        if (isset($search_filter['stop_date']) && $search_filter['stop_date']) {
            $filter['add_time <='] = $search_filter['stop_date'].' 23:59:59';
        }
        $filter['status != '] = 0;

        //an_dump($filter);
        $screen_version_list = array();
        $count = _model('screen_version')->getTotal($filter);
        if ($count) {
            $pager = new Pager($this->per_page);

            $order = 'ORDER BY `id` DESC ';
            $limit = $pager->getLimit($page_no);

            $screen_version_list = _model('screen_version')->getList($filter, $order.' '.$limit);

            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
        }

//         foreach ($screen_version_list as $k => $v) {
//             $screen_version_list[$k]['path']  = str_replace(ROOT_PATH, '', $v['path']);
//         }

        Response::assign('search_filter', $search_filter);
        Response::assign('status', $status);
        Response::assign('screen_version_list', $screen_version_list);

        Response::display('admin/version/index.html');
    }

    /**
     * 添加版本
     */
    public function add()
    {
        Response::display('admin/version/add.html');
    }

    /**
     * 编辑版本
     */
    public function edit()
    {
        $id   = Request::getParam('id', 0);
        if (!$id) {
            return '请选择要编辑的版本';
        }

        $info = _uri('screen_version', $id);
        if (!$info) {
            return '该版本不存在或已删除';
        }

        //$info['path']  = str_replace(ROOT_PATH, '', $info['path']);
        //an_dump($info);
        Response::assign('info', $info);
        Response::display('admin/version/add.html');
    }

    /**
     * 保存版本
     */
    public function save()
    {
        $id          = Request::getParam('id', 0);
        $info        = Request::getParam('info', array());
        $content     = Request::getParam('content', '');
        $status      = Request::getParam('status', 1);

        if (!isset($info['version_no']) || !$info['version_no']) {
            return '版本号不能为空';
        }


        //验证版本号
        if (substr_count($info['version_no'], 'v') !== 1 || strpos($info['version_no'], 'v') !== 0){
            return '版本号格式错误';
        }

        if (!is_numeric(str_replace('v', '', str_replace('.', '', $info['version_no'])))) {
            return '版本号格式错误';
        }

//         if (!isset($info['link']) || !$info['link']) {
//             return '版本地址不能为空';
//         }


        if (!$content) {
            return "请输入简介";
        }
        $name = explode(".", $_FILES['apk_file']['name']);

        if (strtolower($name[count($name)-1]) != 'apk') {
            return '请添加正确的文件';
        }

        //an_dump($info, $_FILES); exit;

        if ($id) {
            $old_info = _uri('screen_version', $id);
            if (!$old_info) {
                return '编辑的版本不存在或已删除';
            }
        }

        $max_size = 1024;
        ini_set('upload_max_filesize', $max_size.'M');
        ini_set('post_max_size', $max_size.'M');

        try {
            if ( !isset($_FILES['apk_file']) || !isset($_FILES['apk_file']['name']) ) {
                throw new Exception('请上传文件');
            }

            $path = ROOT_PATH.'/upload/app';

            if ( !is_dir($path) ) {
                @mkdir($path, 0777, true);
            }

            //$path .= '/'.$_FILES['apk_file']['name'];

            static $count = 1;
            $time = time();
            // u为microseconds，> PHP 5.2.2
            if (version_compare(PHP_VERSION, '5.2.2') >= 0) {
                $current_time = date('YmdHisu', $time);
            } else {
                $current_time = date('YmdHis', $time);
            }

            $random = mt_rand(0, 100);

            $target = $path."/亮靓{$info['version_no']}.apk";

            $count++;

            // 移动上传文件
            if ( !move_uploaded_file($_FILES['apk_file']['tmp_name'], $target) ) {
                return '文件上传失败';
            }

            $info['path']  = str_replace(ROOT_PATH, '', $target);
            $info['size']  = $_FILES['apk_file']['size'];
        } catch ( Exception $e ) {

        }

//         $new_content = preg_replace("/\r\n|\r|\n/","",$content);
//         $str=preg_replace("//","\r\n",$new_content);
//         an_dump($new_content, $str);exit;
        $info['intro']       = $content;

        if ($status == 2) {
            $info['status'] = 2;
        }

        if ($id) {
            _model('screen_version')->update($id, $info);
        } else {
            $id = _model('screen_version')->create($info);
        }

        if ($status == 2) {
            $info = array(
                'title' => '101',
                'mag'   => $content,
                'extras' => array(
                    'size' => screen_helper::get_filesize($_FILES['apk_file']['size']),
                    'version_no' => $info['version_no']
                )
            );
            push_helper::push_msg($info);
        }

        Response::redirect(AnUrl('screen/admin/version'));
    }

    public function upload()
    {
        $max_size = 1024;
        ini_set('upload_max_filesize', $max_size.'M');
        ini_set('post_max_size', $max_size.'M');

        try {
            if ( !isset($_FILES['file']) || !isset($_FILES['file']['name']) ) {
                throw new Exception('请上传文件');
            }

            $path = ROOT_PATH.'/app';

            if ( !is_dir($path) ) {
                @mkdir($path, 0777, true);
            }

            $path .= '/'.$_FILES['file']['name'];

            // 移动上传文件
            if ( !move_uploaded_file($_FILES['file']['tmp_name'], $path) ) {
                throw new Exception('文件上传失败');
            }

            echo json_encode(array('info' => 'ok', 'path' => $path, 'file_name'=>$_FILES['file']['name']));

        } catch ( Exception $e ) {
            echo json_encode(array('info' => $e -> getMessage()));
        }
    }
    /**
     * 删除
     */
    public function delete()
    {
        $id = Request::getParam('id');
        if (!$id) {
            return '对不起，请选择您要删除的版本！';
        }

        $ids = explode(',', trim($id, ','));
        foreach ($ids as $v) {
            $user_info = _uri('screen_version', $v);
            if (!$user_info) {
                continue;
            }

            if ($user_info['status'] == 0) {
            } else {
                _model('screen_version')->update($v, array('status'=>'0'));

            }
        }

        return "ok";
    }

    /**
     * 更改状态
     */
    public function change_status()
    {
        $status = Request::getParam('status', 0);
        $id = Request::getParam('id', 0);

        if (!$id) {
            return '请选择您要操作版本！';
        }

        $res = _model('screen_version')->update($id, array('status'=>$status));

       if ($status == 2) {

           $info = _uri('screen_version', $id);

            $info = array(
                'title' => '101',
                'mag'   => $info['intro'],
                'extras' => array(
                    'size' => screen_helper::get_filesize($info['size']),
                    'version_no' => $info['version_no']
                )
            );
            push_helper::push_msg($info);
        }

        return array('info' => 'ok');
    }

    public function down_load()
    {
        $info = _model('screen_version')->read(array('status'=>1), ' ORDER BY `id` DESC ');
        $url = SITE_URL.$info['path'];

        ob_start();
        $filename=$url;
        $date=date("Ymd-H:i:m");
        header( "Content-type:  application/octet-stream ");
        header( "Accept-Ranges:  bytes ");
        header( "Content-Disposition:  attachment;  filename= 1.apk");
        $size=readfile($filename);
        header( "Accept-Length: " .$size);

    }
}