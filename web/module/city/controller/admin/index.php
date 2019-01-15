<?php

/**
* alltosun.com  index.php
* ============================================================================
* 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
* 网站地址: http://www.alltosun.com
* ----------------------------------------------------------------------------
* 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
* ============================================================================
* $Author: 任锋 (renf@alltosun.com) $
* $date:(2014-6-11 下午04:17:58) $
* $Id$
*/

class Action {
    private $per_page = 30;
    public function __call($action, $params = array())
    {
        $order         = ' ORDER BY `id` DESC ';
        $page          = tools_helper::get('page_no', 1);
        $search_filter = tools_helper::get('search_filter', array());
        $list          = array();
        $filter        = array();
        if (isset($search_filter['name']) && $search_filter['name']) {
            $filter['name LIKE'] = '%'.$search_filter['name'].'%';
        }
        if (isset($search_filter['start_time']) && $search_filter['start_time']) {
            $filter['add_time >='] = $search_filter['start_time'].' 00:00:00';
        }

        if (isset($search_filter['end_time']) && $search_filter['end_time']) {
            $filter['add_time <='] = $search_filter['end_time'].' 23:59:59';
        }

        if (!$filter) {
            $filter[1] = 1;
        }
        $vocher_total = _model('city')->getTotal($filter);
        //分页
        if ($vocher_total) {
            $pager = new Pager($this->per_page);
            $list   = _model('city')->getList($filter, $order.$pager->getLimit($page));

            if ($pager->generate($vocher_total, $page)) {
                Response::assign('pager', $pager);
            }
        }

        // 每个城市的省份
        foreach ($list as $k=>$v) {
            $list[$k]['promary'] = array_values(city_helper::get_promary_list($fix=true, array('id'=>$v['pid'])));
        }

        Response::assign('list', $list);

        Response::display('admin/index.html');
    }

    /**
     * 添加
     */
    public function add()
    {
        Response::display('admin/add.html');;
    }

    /**
     * 保存
     */
    public function save()
    {
       $data = AnForm::parse('admin/add.html');
       if (!$data) {
           return '请正确填写内容！';
       }
       _model('city')->create($data['city']);
       return array('操作成功', 'success', AnUrl("city/admin/"));
    }
}
?>