<?php

/**
 * alltosun.com 文件上传控制器 file_uploader.php
 * ============================================================================
 * 版权所有 (C) 2007-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 高竞竞 (gaojj@alltosun.com) $
 * $Date: 2010-1-25 上午12:49:33 $
*/

/**
 * @tutorial
 * 如果前端上传工具不是flash，且自带的上传名称也不为Filedata的话，必须传入file_field值
 * 如果需要生成缩略图，前台上传工具必须通过POST或者GET方法传入res_name，否则只保存原图
 * @return 默认返回id|path的格式，如果前端上传工具需要接受特定的返回数据，可以在output方法中定义
 */
class Action
{
    private $file_field;
    private $source;
    private $user_id;
    private $res_name;
    private $category_id;
    /**
     * 缩略图类型
     */
    private $type;
    private $max_size_config = array('xheditor'=>122880);
    private $max_size;
    private $module;

    function __construct()
    {
        // 如果Request中有file_field的话，使用Request中的file_field，否则使用flash传来的默认的Filedata
        // xheditor上传使用的filedata
        // ui.fileuploader使用的flash，默认为Filedata
        $this->file_field  = Request::getParam('file_field', 'Filedata');
        $this->source      = Request::getParam('source');
        $this->type        = Request::getParam('type');
        $this->res_name    = Request::getParam('res_name');
        $this->category_id = Request::getParam('category_id', 0);
        $this->module      = Request::getParam('module');

        // 后台管理员操作使用get_admin_id
        $this->user_id     = $this->module == 'admin' ? user_helper::get_admin_id() : user_helper::get_user_id();
        $this->max_size    = isset($this->max_size_config[$this->source]) ? $this->max_size_config[$this->source] : 0;
    }

    function __call($action = '', $params = array())
    {
        // 限制用户登录后才可以上传
        if (empty($this->user_id)) {
            // @FIXME 待用户登录实现后，去除注释
            //$this->output('上传失败，请登录后重试。');
        }

        // html5上传
        $this->html5_upload();

        if (empty($_FILES[$this->file_field])) {
            $this->output('请选择您要上传的文件。');
        }

        // php.ini限制的post大小
        $POST_MAX_SIZE = ini_get('post_max_size');
        $unit = strtoupper(substr($POST_MAX_SIZE, -1));
        $multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

        if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier * (int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
            header("HTTP/1.1 500 Internal Server Error"); // This will trigger an uploadError event in SWFUpload
            $this->output('对不起，上传文件超过服务器限制大小。');
        }

        // 上传配置
        $file_field  = $this->file_field;
        $allow_type  = array_merge(Config::get('allow_image_type'), Config::get('allow_flash_type'));
        $upload_path = UPLOAD_PATH;

        // 上传验证
        $failed_msg = check_upload($_FILES[$file_field], $this->max_size);
        if (!empty($failed_msg)) {
            $this->output($failed_msg);
        }

        // 上传
        $uploadr = new Uploadr($upload_path, $allow_type);
        try {
            $file_path = $uploadr->uploadFile($_FILES[$file_field]['tmp_name']);
        } catch (Exception $e) {
            $this->output($e);
        }

        if (empty($file_path)) {
            $this->output('图片保存失败，请重试');
        }

        // 插入数据库
        $attachment_id = 0;
        $attachment_info = _model('attachment')->read(array('path'=>$file_path));
        if (!empty($attachment_info)) {
            // 如果同一张图片在不同模型中使用，这样处理会导致返回的缩略图比例只是第1个模型产生的
            // 可采用解决方案：图片存放名称不用md5唯一值，改为当前时间+计数，已在Uploadr.php采用该方案
            $attachment_id = $attachment_info['id'];
        } else {
            // 附件类型
            $attachment_type = get_attachment_type($file_path);
            $attachment_info = array(
                'path'       => $file_path,
                'user_id'    => $this->user_id,
                'type'       => $attachment_type,
                'size'       => $_FILES[$file_field]['size'],
                'file_name'  => htmlspecialchars($_FILES[$file_field]['name'], ENT_NOQUOTES)
            );
            $attachment_id = _model('attachment')->create($attachment_info);

            // 缩略图
            // *requires res_name 前台上传工具必须传入res_name
            $res_name    = $this->res_name;
            $category_id = $this->category_id;
            if ($attachment_type == 1) make_thumb($file_path, $res_name, $category_id);
        }

        if (empty($attachment_id)) {
            $this->output('数据库插入失败，请重试');
        }

        $output = array('id'=>$attachment_id, 'file_path'=>$file_path);

        $this->output($output, false);
    }

    /**
     * 转换html5的上传
     * @return bool
     */
    private function html5_upload()
    {
        if (!isset($_SERVER['HTTP_CONTENT_DISPOSITION'])) {
            return false;
        }
        if (!preg_match('/attachment;\s+name="(.+?)";\s+filename="(.+?)"/i', $_SERVER['HTTP_CONTENT_DISPOSITION'], $info)) {
            return false;
        }

        $temp_name = tempnam(sys_get_temp_dir(), 'html5').mt_rand();

        file_put_contents($temp_name, file_get_contents("php://input"));

        $_FILES[$info[1]] = array(
            'name'     => $info[2],
            'tmp_name' => $temp_name,
            'size'     => filesize($temp_name),
            'type'     => '',
            'error'    =>0
        );

        return true;
    }

    /**
     * 处理输出数据，可按照前端不同上传工具返回不同数据结构
     * @param $string 输出信息
     * @param $error 输出信息是否为错误，默认为true
     */
    private function output($info, $error=true)
    {
        $source      = $this->source;
        $res_name    = $this->res_name;
        $category_id = $this->category_id;
        $output      = '';

        if ($source == 'xheditor') {
            /**
             * 1,上传文件域名字为：filedata
             * 2,返回结构必需为json，并且结构如下：{"err":"","msg":"200906030521128703.gif"}
             * 若上传出现错误，请将错误内容保存在err变量中；若上传成功，请将服务器上的绝对或者相对地址保存在msg变量中。
             * 编辑器若发现返回的err变量不为空，则弹出窗口显示返回的错误内容。
             * @var array('err'=>'', 'msg'=>'');
             */
            $xheditor_data = array('err'=>'', 'msg'=>'');
            if ($error) {
                $xheditor_data['err'] = $info;
            } else {
                // 只有最后才返回数据，数据格式为id|path
                // 2010-10-27 gaojj 编辑器上传图片返回全路径，方便内容数据同步到其他网站
                if ($this->type == 'middle') {
                    $xheditor_data['msg'] = SITE_URL._image($info['file_path'], $this->type);
                } else {
                    $xheditor_data['msg'] = SITE_URL._image($info['file_path']);
                }
                // 若返回的地址最前面为半角的感叹号：“!”，表示为立即上传模式，
                // 上传成功后不需要点“确定”按钮，随后自动插入到编辑器内容中。
                if (Request::getParam('immediate', 0) == 1) {
                    $xheditor_data['msg'] = '!'.$xheditor_data['msg'];
                }
            }
            $output = json_encode($xheditor_data);
        } elseif ($source == 'ajaxuploader') {
            // ajax上传返回array('info'=>'ok', 'path'=>'', 'small'=>'', 'middle'=>'',...);
            $json = array();
            if ($error) {
                $json['info'] = $info;
            } else {
                $json['info'] = 'ok';
                // _image处理后返回的数据已经含有上传文件目录路径，不能存入数据库，用file_path存入
//                $json['path'] = _image($info['file_path']);
                $json['path'] = $info['file_path'];
                $thumb_set = get_res_thumb($res_name, $category_id);
                if (!empty($thumb_set)) {
                    foreach ($thumb_set as $k=>$v) {
                        $json[$k] = _image($info['file_path'], $k);
                    }
                }
            }
            $output = json_encode($json);
        } else {
            // 前端flash无法处理JSON或者XML，返回id|path的格式
            if ($error) {
                $output = $info;
            } else {
                $output = $info['id'].'|'.$info['file_path'];
            }
        }
        echo $output;
        exit(0);
    }
}

?>