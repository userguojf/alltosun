<?php

/**
 * alltosun.com 主页面 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2018 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 赵高举 (zhaogj@alltosun.com) $
 * $Date: 2018/5/2 17:52 $
 * $Id$
 *
 */
class Action
{
    private $per_page = 15;
    private $member_id = 0;
    private $member_user = '';
    private $member_res_name = '';
    private $member_res_id = 0;
    private $ranks = 0;

    // 构造方法
    public function __construct()
    {
        $this->member_id = member_helper::get_member_id();
        $member_info = member_helper::get_member_info($this->member_id);
        if ($member_info) {
            $this->member_res_name = $member_info['res_name'];
            $this->member_res_id = $member_info['res_id'];
            $this->ranks = $member_info['ranks'];
            $this->member_user = $member_info['member_user'];
            Response::assign('member_res_name', $this->member_res_name);
            Response::assign('member_res_id', $this->member_res_id);
        } else {
            return '您无权访问此页面';
        }
        Response::assign('curr_member_ranks', $this->ranks);
        Response::assign('member_info', $member_info);
    }

    // 文件列表
    public function __call($action = '', $param = array())
    {
        $search_filter = $filter = array();
        // 筛选 search_filter
        $search_filter = Request::Get('search_filter', array());
        $page_no = Request::Get('page_no', 1);

        $filter = [
            'is_del' => 0
        ];
        if (isset($search_filter['content']) && $search_filter['content']) {
            $filter['`content` LIKE'] = '%' . trim($search_filter['content']) . '%';
        }
        $order = ' ORDER BY `add_time` DESC ';

        $list = [];

        $count = _model('files')->getTotal($filter, $order);
        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
            Response::assign('count', $count);
            // 文件列表
            $list = _model('files')->getList($filter, ' ' . $order . ' ' . $pager->getLimit($page_no));
        }
        Response::assign('list', $list);
        Response::assign('search_filter', $search_filter);
        Response::assign('page', $page_no);
        Response::display('admin/file/list.html');
    }

    // 保存文件
    public function js_save()
    {
        //获取自定义文件名
        $content = Request::POST('content', '');
        // 获取文件号
        $file_number = Request::POST('file_number', '');
        // 获取印发时间
        $print_time = Request::POST('print_time', '');
        //获取上传者id
        $member_id = $this->member_id;
        //获取上传者账号
        $member_user = $this->member_user;

        //上传文件的处理
        if (!$_FILES['info']['tmp_name']) {
            return '请刷新网络重试！';
        }

        //文件大小单位的计算
        if ($_FILES['info']['size'] / 1024 < 1024) $size = round($_FILES['info']['size'] / 1024, 1) . 'KB';
        if ($_FILES['info']['size'] / 1024 > 1024) $size = round($_FILES['info']['size'] / 1024 / 1024, 1) . 'M';

        //文件名的获取
        $file_name = substr($_FILES['info']['name'], 0, strrpos($_FILES['info']['name'], '.'));

        //判断是否设置自定义文件名
        if (!$content) {
            $content = $file_name;
        }

        //后缀名
        $file_suffix = substr($_FILES['info']['name'], strrpos($_FILES['info']['name'], '.') + 1);

        //查看重名处理
        if (strrpos($file_name, '_')) {
            //有 重新赋值
            $file_name = substr($file_name, 0, strrpos($file_name, '_'));
        }
        // 引用上传

        $path = file_apply_helper::upload_file($_FILES['info'], false, $member_user);
        // $path = upload_file($_FILES['info'], false, $member_user);
        // 创建
        $add_time = date('Y-m-d H:i:s', time());
        $con = [
            'member_id' => $member_id,
            'content' => $content,
            'suffix' => $file_suffix,
            'path' => $path,
            'size' => $size,
            'add_time' => $add_time,
            'member_user' => $member_user,
            'file_number' => $file_number,
            'print_time' => $print_time
        ];
        _model('files')->create($con);
    }

    // video文件跳转
    public function video_redirect()
    {
        $id = Request::Get('file_id', 0); // 文件id
        if (!$id) {
            return 'id不存在';
        }

        // $file_info = _uri('files', $id);
        // 筛选条件
         $filter = [
             'is_del' => 0,
             'id' => $id
         ];
         $file_info = _model('files')->read($filter);
        if (!$file_info) {
            return '信息不存在';
        }
        // 跳转链接
        $pdf_url = AnUrl() . '/upload' . $file_info['path'];

        Response::assign('pdf_url', $pdf_url);
        Response::assign('file_info', $file_info);
        Response::display('admin/file/video_show.html');

    }

    // 下载
    public function download()
    {
        $id = Request::Get('id', 0);

        if (!$id) {
            return 'id不存在';
        }

        $load_info = _uri('files', $id);
        if (!$load_info) {
            return '信息不存在';
        }
        $path = 'upload' . $load_info['path'];
        ob_start();

        $filename = pathinfo($path); //文件名
        $tmp_filename = $filename['basename'];

        //获取后缀
        $file_suffix = substr($filename['basename'], strrpos($filename['basename'], '.') + 1);

        //下载文件名
        $filename['basename'] = $load_info['content'] . '.' . $file_suffix;

        //判断是否为文档
        if ($file_suffix == 'msoffice') {
            // $filename['basename']=str_replace(".zip.msoffice",".docx",$tmp_filename);
            $filename['basename'] = $load_info['content'] . '.docx';
        }

        $con = [
            'member_id' => $this->member_id,
            'file_id' => $id,
            'res_name' => $this->member_res_name,
            'ranks' => $this->ranks,
            'res_id' => $this->member_res_id,
            'status2' => 1,
            'dl_count' => 1,
        ];

        // 创建文件操作记录
        $info = _uri('file_record', array('member_id' => $this->member_id, 'file_id' => $id));
        // 集团管理员不计入操作记录
        if ($this->member_res_name != 'group') {
            if (!$info) {
                _model('file_record')->create($con);
            } else {
                // 条件
                $where = [
                    'member_id' => $this->member_id,
                    'file_id' => $id,
                ];
                // 更新的字段
                $up = [
                    'update_time' => date('Y-m-d H:i:s', time()),
                    'status2' => 1, //将状态更改为已下载
                ];
                _model('file_record')->update($where, $up);
                _model('file_record')->create(array('id' => $info['id']), 'ON DUPLICATE KEY UPDATE dl_count=dl_count+1');

            }
            // 文件下载次数+1
            // _model('files')->create(array('id' => $id), 'ON DUPLICATE KEY UPDATE dl_count=dl_count+1');
        }
        header("Content-type:  application/octet-stream ");
        header("Accept-Ranges:  bytes ");
        header("Content-Disposition:  attachment;  filename= {$filename['basename']}");
        $size = readfile($path);
        header("Accept-Length: " . $size);
    }

    /**
     * 文件操作记录列表
     * @return string
     */
    public function record_list()
    {
        $id = Request::Get('id'); // 文件id
        $page_no = Request::Get('page_no', 1);
        $search_filter = Request::Get('search_filter', array());
        $is_export = Request::Get('is_export', 0);

        if (!$id) {
            return "ID不存在!";
        }

        $file_info = _uri('files', $id); // 文件详情
        if (!$file_info) {
            return '文件不存在';
        }

        $order = ' ORDER BY `id`';

        $filter = [
            'res_name' => $search_filter['pc'], // province:省份营业厅 city城市营业厅
        ];

        if (isset($search_filter['province']) && $search_filter['province']) {
            $filter['res_id'] = $search_filter['province'];
            $city_list = _model('city')->getList(array('province_id' => $search_filter['province']));
            Response::assign('city_list', $city_list);
        }

        if (isset($search_filter['city']) && $search_filter['city']) {
            $filter['res_id'] = $search_filter['city'];
        }

        // 查看筛选
        if (isset($search_filter['status']) && $search_filter['status']) {
            $memberids = file_apply_helper::get_record_member_ids($filter, $id, 'status', $search_filter['status']);
            if (!empty($memberids)) {
                $filter['id'] = $memberids;
            } else {
                $filter['id'] = '';
            }
        }

        // 下载筛选
        if (isset($search_filter['status2']) && $search_filter['status2']) {
            $memberids = file_apply_helper::get_record_member_ids($filter, $id, 'status2', $search_filter['status2']);
            if ($memberids) {
                $filter['id'] = $memberids;
            } else {
                $filter['id'] = '';
            }
        }

        // 获取已经查看过文件的用户
        if ((isset($search_filter['status']) && $search_filter['status']) && (isset($search_filter['status2']) && $search_filter['status2']) ) {
            if ($search_filter['status'] == 1) {
                $status = 1;
            } else {
                $status = 0;
            }
            if ($search_filter['status2'] == 1) {
                $status2 = 1;
            } else {
                $status2 = 0;
            }
            $memberids = file_apply_helper::get_see_down_info($filter, $id, $status, $status2);
            if ($memberids) {
                $filter['id'] = $memberids;
            } else {
                $filter['id'] = '';
            }
        }
        $member_list = [];
        $count = _model('member')->getTotal($filter, $order);
        // 导出
        if ($is_export) {
            $export_list = _model('member')->getList($filter, ' ' . $order);
            $this->export($export_list, $search_filter['pc'], $id);
        }

        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }

            Response::assign('count', $count);
            $member_list = _model('member')->getList($filter, ' ' . $order . ' ' . $pager->getLimit($page_no));
        }

        // 有管理员的省份列表
        $sql = 'select distinct res_id from member where res_name="province" order by res_id';
        $province_list = _model('member')->getAll($sql);

        Response::assign('id', $id);
        Response::assign('file_info', $file_info);
        Response::assign('member_list', $member_list);
        Response::assign('search_filter', $search_filter);
        Response::assign('province_list', $province_list);
        Response::assign('page', $page_no);

        Response::display('admin/file/list_record.html');
    }

    /**
     * 导出
     * @param $list
     * @param $pc 区分省或市 province省 city市
     * @param $fileid 文件id
     * @return string
     */
    public function export($list, $pc, $fileid)
    {
        if (!$list) {
            return '暂无数据';
        }

        foreach ($list as $k => $v) {
            if ($pc == 'province') {
                $info[$k]['proinvce'] = _uri('province', $v['res_id'], 'name'); // 省份
                $info[$k]['city'] = '省管理员'; // 市
            } else {
                $provinceid = _uri('city', $v['res_id'], 'province_id');
                $info[$k]['proinvce'] = _uri('province', $provinceid, 'name'); // 省份
                $info[$k]['city'] = _uri('city', $v['res_id'], 'name'); // 市
            }
            // 管理员名称
            $info[$k]['member_name'] = file_apply_helper::get_member_name_phone($v['id'], 'user_name');
            // 管理员手机号
            $info[$k]['member_phone'] = file_apply_helper::get_member_name_phone($v['id'], 'phone');
            // 是否查看过文件
            $info[$k]['status'] = file_apply_helper::check_member_status($v['id'], $fileid, 'status');
            // 是否下载过文件
            $info[$k]['status2'] = file_apply_helper::check_member_status($v['id'], $fileid, 'status2');
            $date = file_apply_helper::get_record_update($v['id'],$fileid);
            $info[$k]['last_record_time'] = $date['date'];

        }
        if ($pc == 'province') {
            $params['filename'] = '省管理员';
        } else {
            $params['filename'] = '市管理员';
        }
        $params['data'] = $info;
        $params['head'] = array('省', '市', '管理员姓名', '手机号', '查看', '下载', '最后一次操作时间');

        Csv::getCvsObj($params)->export();
    }

    public function test_send_msg()
    {
        $content = array(
            'param1' => 112233,
        );
        $where = [
            'province',
            'city',
            1
        ];
        $memberphones = _model('member')->getCol("SELECT bu.phone FROM `business_hall_user` as bu, member as mb WHERE bu.member_id = mb.id AND mb.res_name in (?,?) AND mb.status = ?",$where);
        // p($memberphones);
       /* $params['tel'] = '18813044687';
        $params['content'] = json_encode($content);
        $params['template_id'] = 91554166;

        $msg_res = _widget('message')->send_message($params);*/
        // p($msg_res);
    }

    /*********************************************************************/
}