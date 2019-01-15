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
     * 返回用户的最后一次操作时间
     * @param $memeberid 用户id
     * @param $fileid 文件id
     * @return array
     */
    public static function get_record_update($memeberid, $fileid)
    {
        $where = [
            'member_id' => $memeberid,
            'file_id' => $fileid,
        ];
        $record = _uri('file_record', $where, 'update_time');
        $info = [];
        if ($record) {
            $info = [
                'code' => '1',
                'date' => date('Y-m-d H:i', strtotime($record)),
            ];
        } else {
            $info = [
                'code' => '0',
                'date' => '暂无操作记录',
            ];
        }
        return $info;
    }

    /**
     * 检测用户是否查看下载
     * @param $memeberid
     * @param $fileid
     * @param $type status为用户是否查看 status2为用户是否下载
     * @return string
     */
    public static function check_member_status($memeberid, $fileid, $type)
    {
        $status = _uri('file_record', [
            'member_id' => $memeberid,
            'file_id' => $fileid,
             $type => 1
        ],'update_time');
        if ($status) {
            return '是';
        } else {
            return '否';
        }
    }
    /**
     * 获取是否查看/下载的管理员ids
     * @param array $filter 传入的筛选
     * @param $fileid 文件id
     * @param $type 类型，status为是否查看 status2为是否下载
     * @param $status 1是  2否
     * @return array 返回符合条件用户id
     */
    public static function get_record_member_ids(array $filter, $fileid, $type, $type_value)
    {
        unset($filter['id']);
        $members = _model('member')->getList($filter);
        $memberids_record = $members_norecord = [];
        foreach ($members as $v) {
            $where = [
                'member_id' => $v['id'],
                'file_id' => $fileid,
                 $type => 1
            ];
            $info = _uri('file_record', $where);
            if ($info) {
                $memberids_record[] = $v['id'];
            } else {
                $members_norecord[] = $v['id'];
            }
        }

        if ($type_value == 1) {
            return $memberids_record;
        } else {
            return $members_norecord;
        }
    }


    // 获取查看和下载条件同时存在的数据
    public static function get_see_down_info(array $filter, $fileid, $status, $status2)
    {
        unset($filter['id']);
        $members = _model('member')->getList($filter);
        $memberids_record = $members_norecord = [];
        $statuss = $status;
        $status2s = $status2;
        if ($status == 0 && $status2 == 0) {
            $statuss = [1,0];
            $status2s = [1,0];
        }
        foreach ($members as $v) {
            $where = [
                'member_id' => $v['id'],
                'file_id' => $fileid,
                'status' => $statuss,
                'status2' => $status2s
            ];
            $info = _uri('file_record', $where);
            if ($info) {
                $memberids_record[] = $v['id'];
            } else {
                $members_norecord[] = $v['id'];
            }
        }

        if ($status == 0 && $status2 == 0) {
            return $members_norecord;
        } else {
            return $memberids_record;
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
            $type1 = '<span class="gallery"><a class="online_show" href="' . _image($file_info['path'], 'middle') . '" >' . $file_name . '</a></span>';
            $type2 = '<span class="gallery"><a class="online_show btn-option" title="在线查看" href="' . _image($file_info['path'], 'middle') . '"><i class="icon-search2"></i></a></span>';
        } else if ($file_info['suffix'] == 'pdf') {
            $type1 = '<a class="online_show pdf" pdf_name = "'.$file_name.'" file_id = "'.$file_info['id'].'" href="javascript:void(0)" pdf_href="' . _image($file_info['path'])  . '">' . $file_name . '</a>';
            $type2 = '<a class="online_show btn-option pdf" pdf_name = "'.$file_name.'" file_id = "'.$file_info['id'].'" title="在线查看" href="javascript:void(0)" pdf_href="' . _image($file_info['path']) . '"><i class="icon-search2"></i></a>';
        } else if ($file_info['suffix'] == 'mp4') {
            $url = AnUrl('file_apply/admin/file/video_redirect?file_id=' . $file_info['id']);
            $type1 = '<a class="online_show" target="_blank"  href="' . $url  . '">' . $file_name . '</a>';
            $type2 = '<a class="online_show btn-option" target="_blank" title="在线查看" href="' . $url . '" ><i class="icon-search2"></i></a>';
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
     * 根据文件id获取下载和浏览总次数
     * @param $fileid
     * @return array
     * @throws AnException
     */
    public static function get_dl_view_count_by_fileid($fileid, $res_name)
    {
        // 下载总次数
        $dl_count_total = _model('file_record')->getAll("select sum(dl_count) as total from file_record where file_id=".$fileid." and res_name = '".$res_name."'");
        $view_count_total = _model('file_record')->getAll("select sum(view_count) as total from file_record where file_id=".$fileid." and res_name = '".$res_name."'");
        $fileinfo = _uri('file_record',array('file_id'=>$fileid, 'res_name'=>$res_name));
        if (!$fileinfo) {
            $info['dl_total'] = 0;
            $info['view_total'] = 0;
        } else {
            $info['dl_total'] = $dl_count_total[0]['total'];
            $info['view_total'] = $view_count_total[0]['total'];
        }

        return $info;
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
    
    
    /**
     * 获取对应省审批人
     * @param unknown $province_id
     * @return boolean
     */
    public static function get_province_member_user($province_id)
    {
        if(!$province_id){
            return false;
        }
        
        return _uri('member', array('res_id'=>$province_id,'res_name' => 'province'), 'member_user');
    }
    
    
    public static function get_number($cid){
        if(!$cid){
            return false;
        }
        $filter = array(
                'cid' => $cid 
        );
        
        $apply_ids = _model('apply')->getFields('id',$filter);
        //所有个数
        $num_all = count($apply_ids);
        //省不通过的个数
        $no_province_num = _model('convert_record')->getTotal(array('apply_id' => $apply_ids,'check_status' => 2));
        $num = $num_all - $no_province_num;
        return $num;
    }
    
    public static function get_apply_status_by_cid($cid)
    {
        $record_list = [];
        $filter = array(
                'cid' => $cid
        );
        
        $apply_ids = _model('apply')->getFields('id',$filter);
        //是否存在通过的
        $record_list = _model('convert_record')->getList(array('apply_id' => $apply_ids,'check_status' => 1));
        
        return $record_list;
    }
    
    
    /**
     * 根据member_id 获取用户电话
     * @param unknown $member_id
     * @return boolean|unknown
     */
    public static function get_apper_phone($member_id=0,$business_id = '',$member_user ='')
    {
        if(!$member_id && !$business_id && !$member_user){
            return false;
        }
        $phone = '';
        if($member_id && !$business_id){
            $info = _model('business_hall_user')->getFields('phone',array('member_id' => $member_id));
            $phone = $info[0];
        }
        
        if ($business_id){
           
            $info = _model('business_hall')->getFields('contact_way',array('id' => $business_id));
            $phone = $info[0];
        }
       
        if ($member_user){
            $member_id = _model('member')->read('id',array('member_user' => $member_user));
            $info = _model('business_hall_user')->getFields('phone',array('member_id' => $member_id));
            $phone = $info[0];
        }
        return $phone;
    }
    
    
    public static  function get_store($store_type)
    {
        $type = '';
        if($store_type == 1){
            $type = '一级厅';
        }else if($store_type == 2){
            $type = '二级厅';
        }else if($store_type == 3){
            $type = '三级厅';
        }else if($store_type == 4){
            $type = '四级厅';
        }else if($store_type == 5){
            $type = '五级厅';
        }else if($store_type == 6){
            $type = '其他';
        }else{
            $type = '请选择';
        }
        
        return $type;
    }
}
