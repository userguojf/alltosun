<?php

/**
 * alltosun.com 短信日志 message.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 沈飞 (shenf@alltosun.com) $
 * $Date: 2016-4-18 下午12:55:53 $
 * $Id: message.php 375690 2017-10-20 10:27:36Z shenxn $
 */

class Action
{
    private $per_page = 30;

    /**
     * 操作日志信息列表
     * @param unknown_type $action
     * @param array() $params
     */
    public function __call($action = '', $params = array())
    {
        $order         = ' ORDER BY `id` DESC ';
        $page          = Request::Get('page_no', 1);
        $search_filter = Request::Get('search_filter', array());
        $filter        = array();
        $list          = array();

        // 日志编号
        if (isset($search_filter['id']) && $search_filter['id']) {
            $filter['id'] = $search_filter['id'];
        }

        // 操作人手机号
        if (isset($search_filter['phone']) && $search_filter['phone']) {
            $filter['phone'] = $search_filter['phone'];
        }

        // 操作时间
        if (isset($search_filter['add_time']) && $search_filter['add_time']) {
            $filter['add_time <='] = $search_filter['add_time'].' 00:00:00';
        }


        if (isset($search_filter['search_type'])) {
            if ($search_filter['search_type'] == 0) {
                $filter['res_code'] = 0;
            } elseif ($search_filter['search_type'] == 1) {
                 $filter['res_code >'] ='0';
            }
        }

        if (!$filter) {
            $filter = array( 1 => 1);
        }

        $log_total = _model('message_log')->getTotal($filter);

        // 分页
        if ($log_total) {
            $pager = new Pager($this->per_page);
            $list  = _model('message_log')->getList($filter, $order . $pager->getLimit($page));

            if ($pager->generate($log_total, $page)) {
                Response::assign('pager', $pager);
            }
        }

        Response::assign('search_filter', $search_filter);
        Response::assign('list', $list);
        Response::assign('total', $log_total);
        Response::assign('search_filter', $search_filter);
        Response::display('admin/message.html');
    }

    function substr_cut($str_cut,$length)
    {
        if (strlen($str_cut) > $length)
        {
            for($i=0; $i < $length; $i++)
                if (ord($str_cut[$i]) > 128)    $i++;
            $str_cut = substr($str_cut,0,$i)."}";
        }
        return $str_cut;
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

            _model('message_log')->delete($v);

        }

        return array('info'=>'ok');
    }

    /**重发数据
     * @return
     * @throws AnException
     */

    public function message_rest()
    {
        $id = Request::Post('id', 0);

        if (!$id) {
            return array('info' => 'ID不存在');
        }

        $message_info = _uri('message_log',$id);

        $params = [];

        //手机号
        $params['tel']         = $message_info['phone'];
        $params['template_id'] = $message_info['temp_id'];
        $content               = json_decode($message_info['content'],true);
        $params['content']     = $content['content'];

        _widget('message')->rest_message($params,$message_info['id']);

        return 'ok';
    }
}