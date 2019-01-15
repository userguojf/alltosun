<?php

/**
 * alltosun.com 操作日志列表控制器 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址：http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 孙先水 (sunxs@alltosun.com) $
 * $Date: 2015-12-18 下午03:13:59 $
*/

class Action
{
    private $per_page = 30;
    /**
     * 操作日志信息列表
     * @param unknown_type $action
     * @param array() $params
     */
    public function __call($action, $params = array())
    {
        $order         = ' ORDER BY `id` DESC ';
        $page          = tools_helper::get('page_no', 1);
        $search_filter = tools_helper::get('search_filter', array());
        $filter        = array();
        $list          = array();

        // 日志编号
        if (isset($search_filter['id']) && $search_filter['id']) {
            $filter['id'] = $search_filter['id'];
        }

        // 操作人
        if (isset($search_filter['member_id']) && $search_filter['member_id']) {
            $filter['member_id'] = $search_filter['member_id'];
        }

        // 资源表
        if (isset($search_filter['res_name']) && $search_filter['res_name']) {
            if (mb_strlen($search_filter['res_name'], 'utf-8') != strlen($search_filter['res_name'])) {
                $key = array_search($search_filter['res_name'], log_config::$log_res_name);
                if ($key) {
                    $filter['res_name'] = $key;
                } else {
                    $filter['res_name'] = '';
                }
            } else {
                $filter['res_name'] = $search_filter['res_name'];
            }
        }

        // 资源ID
        if (isset($search_filter['res_id']) && $search_filter['res_id']) {
            $filter['res_id'] = $search_filter['res_id'];
        }

        // action
        if (isset($search_filter['action']) && $search_filter['action'] != '请选择') {
            $filter['action'] = $search_filter['action'];
        }

        // IP操作
        if (isset($search_filter['ip']) && $search_filter['ip']) {
            $filter['ip'] = $search_filter['ip'];
        }

        // 操作时间
        if (isset($search_filter['start_time']) && $search_filter['start_time']) {
            $filter['add_time >='] = $search_filter['start_time'].' 00:00:00';
        }

        if (isset($search_filter['end_time']) && $search_filter['end_time']) {
            $filter['add_time <='] = $search_filter['end_time'].' 23:59:59';
        }

        if (!$filter) {
            $filter = array(1=>1);
        }

        $log_total = _model('log')->getTotal($filter);

        // 分页
        if ($log_total) {
            $pager = new Pager($this->per_page);
            $list  = _model('log')->getList($filter, $order . $pager->getLimit($page));
            if ($pager->generate($log_total, $page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('list', $list);
        Response::assign('total', $log_total);
        Response::assign('search_filter', $search_filter);
        Response::display('admin/index.html');
    }

    /**删除数据
     * @return array
     * @throws AnException
     */
    public function delete()
    {
        $id = Request::getParam('id');

        if (!$id) {
            return array('info'=>'要删除的ID不存在');
        }
        $ids = explode(',', trim($id, ','));
        foreach ($ids as $k => $v) {
            _model('log')->delete($v);
        }
        return array('info'=>'ok');
    }      
}
?>