<?php
/**
 * alltosun.com
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2018年5月2日: 2016-7-26 下午3:05:10
 * Id
 */

class file_apply_helper
{
    /**
     * 连接文件名和后缀名
     * @param $file_name
     * @param $suffix
     * @return string
     */
    public static function file_suffix($file_name, $suffix)
    {
        return $file_name . '.' . $suffix;
    }

    /**
     * 上传文件
     * @param $file_info
     * @param bool $is_attachment
     * @param string $res_name
     * @return bool|string
     * @throws Exception
     */
    public static function upload_file($file_info, $is_attachment = false, $res_name = 'admin')
    {
        include 'Uploadr.php';
        // php.ini限制的post大小
        $POST_MAX_SIZE = ini_get('post_max_size');
        $unit = strtoupper(substr($POST_MAX_SIZE, -1));
        $multiplier = ($unit == 'M' ? 1048576 : ($unit == 'K' ? 1024 : ($unit == 'G' ? 1073741824 : 1)));

        if ((int)$_SERVER['CONTENT_LENGTH'] > $multiplier * (int)$POST_MAX_SIZE && $POST_MAX_SIZE) {
            header("HTTP/1.1 500 Internal Server Error"); // This will trigger an uploadError event in SWFUpload
            throw new Exception('对不起，上传文件超过服务器限制大小。');
        }

        //允许上传的文件类型
        $allow_type = file_apply_config::$allow_type;

        // 上传验证
        $failed_msg = check_upload($file_info, 8000000);

        if (!empty($failed_msg)) {
            throw new Exception($failed_msg);
        }

        // 上传
        if (SAE) {
            require_once MODULE_CORE . '/helper/Sae_uploader.php';
            $uploader = new Sae_uploader(Config::get('storage_domains'));
        } else {
            //企业号培训add
            if (ONDEV) {
                $uploader = new Uploadr(UPLOAD_FOLDER, $allow_type);
            } else {
                $uploader = new Uploadr(DOWNLOAD_PATH, $allow_type);
            }
        }


        try {
            $file_path = $uploader->uploadFile($file_info['tmp_name']);
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }

        if (empty($file_path)) {
            throw new Exception('图片保存失败，请重试');
        }

        if (!$is_attachment) {
            if ($res_name) make_thumb($file_path, $res_name);
            return $file_path;
        }

    }

    /**
     * 返回时间到分
     * @param $date
     * @return false|string
     */
    public static function mi_date($date)
    {
        return date('Y-m-d H:i', strtotime($date));
    }

    /**
     * 获取business_hall_user表管理员的名称和手机号，如果没有就返回member表里member_user
     * @param $memberid 用户id
     * @param string $field 字段名
     * @return array
     */
    public static function get_member_name_phone($memberid, $field = '')
    {
        $info = _uri('business_hall_user', array('member_id' => $memberid), $field);

        if ($info) {
            return $info;
        } else {
            if ($field == 'user_name') {
                return _uri('member', $memberid, 'member_user');
            }
            return '暂无';
        }
    }

    /**
     * 检查用户是否有过当前文件操作记录
     * @param $memeberid 用户id
     * @param $fileid 文件id
     * @return array
     */
    public static function check_member_record($memeberid, $fileid)
    {
        $record = _uri('file_record', array('member_id' => $memeberid, 'file_id' => $fileid), 'update_time');
        $info = [];
        if ($record) {
            $info = [
                'code' => 1,
                'res' => '是',
                'update_time' => date('Y-m-d H:i', strtotime($record)),
            ];
            return $info;
        }
        $info = [
            'code' => 0,
            'res' => '否',
            'update_time' => '无操作记录'
        ];
        return $info;
    }

    /**
     * 获取是否查看/下载的管理员ids
     * @param array $filter 传入的筛选
     * @param $fileid 文件id
     * @param $status 1是  2否
     * @return array 返回符合条件用户id
     */
    public static function get_record_member_ids(array $filter, $fileid, $status)
    {
        $members = _model('member')->getList($filter);
        $memberids_record = $members_norecord = [];
        foreach ($members as $v) {
            $res = self::check_member_record($v['id'], $fileid);
            if ($res['code']) {
                $memberids_record[] = $v['id'];
            } else {
                $members_norecord[] = $v['id'];
            }
        }
        if ($status == 1) {
            return $memberids_record;
        } else {
            return $members_norecord;
        }
    }

    /**
     * 根据文件内容返回HTML元素
     * @param $file_info 文件内容
     * @param $type 1文件名列  2操作列 查看按钮
     * @return string
     */
    public static function get_content_html_by_suffix($file_info, $type)
    {
        $img_type = [
            '0' => 'jpg',
            '1' => 'png',
            '2' => 'gif',
            '3' => 'jpeg',
        ];
        $file_name = self::file_suffix($file_info['content'], $file_info['suffix']);
        if (in_array($file_info['suffix'], $img_type)) {
            $type1 = '<a class="online_show" href="' . _image($file_info['path'], 'middle') . '" target="_blank">' . $file_name . '</a>';
            $type2 = '<a class="online_show btn-option" title="在线查看" href="' . _image($file_info['path'], 'middle') . '" class="btn-option" target="_blank"><i class="icon-search2"></i></a>';
        } else if ($file_info['suffix'] == 'pdf') {
            $url = AnUrl('file_apply/admin/file/pdf_redirect?file_id=' . $file_info['id']);
            $type1 = '<a class="online_show" href="' . $url . '" target="_blank">' . $file_name . '</a>';
            $type2 = '<a class="online_show btn-option" title="在线查看" href="' . $url . '" target="_blank"><i class="icon-search2"></i></a>';
        } else {
            $type1 = $file_name;
            $type2 = '<a href="javascript:void(0);" title="在线查看" class="btn-option"><i class="icon-search2"></i></a>';
        }

        if ($type == 1) {
            return $type1;
        } else {
            return $type2;
        }


    }

    /**
     * 整理多图上传数据格式为一维
     * @param unknown $file
     * @return array
     */
    public static function file_info($file)
    {

        $arr = array();
        $count = count($file['tmp_name']);
        for ($i = 0; $i < $count; $i++) {
            $arr['tmp_name'] = $file['tmp_name'][$i];
            $arr['name'] = $file['name'][$i];
            $arr['type'] = $file['type'][$i];
            $arr['error'] = $file['error'][$i];
            $arr['size'] = $file['size'][$i];
        }
        return $arr;
    }


    /**
     * 根据member_id获取用户账号
     * @param unknown $member_id
     * @return boolean
     */
    public static function get_user_by_memberid($member_id)
    {
        if (!$member_id) {
            return false;
        }
        $member_info = _model('member')->read($member_id);
        return $member_info['member_user'];
    }
    
  


   
    
    
    
    /**
     * 通过id 返回审核信息
     * @param unknown $status
     * @return string
     */
    public static function get_checker_by_apply_id($apply_id)
    {
        //审核状态 0 未审核 1 省审核通过 2省审核不通过 3集团通过 4集团不通过
        if(!$apply_id){
            return false;
        }
    
        $rescord_info = _model('convert_record')->read(array('apply_id' => $apply_id));
    
    
        return $rescord_info;
    
    }
    
    
    /**
     * 通过id 返回审核状态
     * @param unknown $status
     * @return string
     */
    public static function get_check_status_by_apply_id($apply_id)
    {
        //审核状态 0 未审核 1 省审核通过 2省审核不通过 3集团通过 4集团不通过
        if(!$apply_id){
            return false;
        }
        $rescord_info = _model('convert_record')->read(array('apply_id' => $apply_id));
        $rescord_info['rescord_status'] = '待审核';
        $status = $rescord_info['check_status'];
        if($status == 0){
            $rescord_info['rescord_status'] = '待审核';
        }else if($status == 1){
            $rescord_info['rescord_status'] = '审核中';
        }else if($status == 2 || $status == 4){
            $rescord_info['rescord_status'] = '未通过';
        }else if($status == 3){
            $rescord_info['rescord_status'] = '通过';
        }
        
        return $rescord_info;
    
    }
    
    
    
}
