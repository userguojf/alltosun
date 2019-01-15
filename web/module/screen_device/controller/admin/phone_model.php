<?php
/**
 * alltosun.com  机型管理
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2017年12月12日: 2016-7-26 下午3:05:10
 * Id
 */


class Action
{
    private $per_page = 20;
    private $member_id  = 0;
    private $member_res_name = '';
    private $member_res_id   = 0;
    private $member_info     = array();
    private $ranks           = 0;

    public function __construct()
    {

        $this->member_id   = member_helper::get_member_id();

        $this->member_info = member_helper::get_member_info($this->member_id);

        if ($this->member_info) {
            $this->member_res_name = $this->member_info['res_name'];
            $this->member_res_id   = $this->member_info['res_id'];
            $this->ranks           = $this->member_info['ranks'];
        }

        Response::assign('curr_member_ranks', $this->ranks);
    }

    public function __call($action = '', $params = array())
    {
        $search_filter    = tools_helper::get('search_filter', array());
        $order            = ' ORDER BY `id` DESC ';
        $groupby          = 'GROUP BY `phone_name`,`phone_version` ';
        $page          = tools_helper::get('page_no', 1);
        $filter = array(1 => 1);

        if (!empty($search_filter['phone_name'])) {
            $filter['phone_name LIKE '] = '%'.$search_filter['phone_name'].'%';
        }

        if (!empty($search_filter['phone_version'])) {
            $filter['phone_version LIKE '] = '%'.$search_filter['phone_version'].'%';
        }
        //status 0 未通过 1 通过
        if (isset($search_filter['status'])) {
            if ($search_filter['status'] == 1) {
                $filter['status'] = $search_filter['status'];
            } else if ($search_filter['status'] == 2) {
                $filter['status'] = 0;
            }
        }

        $device_list = array();

        $count = _model('screen_device_nickname')->getTotal($filter);
        if ($count) {
            $pager = new Pager($this->per_page);
            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }
            $device_list = _model('screen_device_nickname')->getList($filter, ' '.$order.' '.$pager->getLimit($page));
        }
        Response::assign('count', $count);
        Response::assign('search_filter', $search_filter);
        Response::assign('device_list', $device_list);
        Response::display('admin/phone_model/device_list.html');

    }

    public function update_device_nickname()
    {
        $device_list = _model('screen_device')->getList(array(1=>1), ' GROUP BY `phone_name`, `phone_version` ');

        foreach ($device_list as $k => $v) {
            $filter = array('phone_name' => $v['phone_name'], 'phone_version' => $v['phone_version']);

            $info  = _model('screen_device_nickname')->read($filter);

            if (!$info) {
                $new_data = $filter;
                $new_data['name_nickname'] = $v['phone_name_nickname'];
                $new_data['version_nickname'] = $v['phone_version_nickname'];
                _model('screen_device_nickname')->create($new_data);
            } else {
                $update = array();
                if ($v['phone_name_nickname']) {
                    $update['name_nickname'] = $v['phone_name_nickname'];
                }

                if ($v['phone_version_nickname']) {
                    $update['version_nickname'] = $v['phone_version_nickname'];
                }

                if ($update) {
                    _model('screen_device_nickname')->update($info['id'], $update);
                }
            }
        }
    }

}