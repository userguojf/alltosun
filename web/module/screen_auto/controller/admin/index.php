<?php
/**
 * alltosun.com  index.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-1-8 上午10:10:47 $
 * $Id$
 */

class Action
{
    private $per_page    = 20;
    private $member_info = array();

    public function __call($action = '', $params = array())
    {
        $type             = Request::Get('type', 0);
        $business_hall_id = Request::Get('business_hall_id', 0);

        $search_filter = Request::Get('search_filter', array());
        $page          = Request::get('page_no' , 1) ;

        $filter = $list =array();

        if ( isset($search_filter['auto_start']) ) {
            if (( int )$search_filter['auto_start'] === 0 || in_array($search_filter['auto_start'], array(1, 2))) {
                $filter['auto_start'] = $search_filter['auto_start'];
                $search_filter['auto_start'] = ( int )$search_filter['auto_start'];
            } else {
                unset($search_filter['auto_start']);
            }
        }

        if (isset($search_filter['business_hall_title']) && $search_filter['business_hall_title']) {
            $business_hall_info = _model('business_hall')->read(array('title' => $search_filter['business_hall_title']));
            // 
            if (!$business_hall_info) return '请输入正确的营业厅名称';

            $filter['business_hall_id'] = $business_hall_info['id'];
        } 

        if ( isset($search_filter['device_unique_id']) && $search_filter['device_unique_id'] ) {
            $filter['device_unique_id'] = $search_filter['device_unique_id'];
        }
        // 过滤掉我谷歌测试的

        if ( $type ) {
            if ( !$business_hall_id ) return '参数丢失';
            $business_hall_info = _model('business_hall')->read(array('id' => $business_hall_id));

            if (!$business_hall_info) return '未找到营业厅';

            $filter['business_hall_id'] = $business_hall_id;
        }

        $filter['status'] = 1;
//         if ( !$filter ) $filter = array(1 => 1);

        $order          = " ORDER BY `id` DESC ";

        $count = _model('screen_auto_start')->getTotal($filter);

        if ($count) {
            $pager = new Pager($this->per_page);
            $list = _model('screen_auto_start')->getList($filter, $order.$pager->getLimit($page));

            if ($pager->generate($count,$page)) {
                Response::assign('pager', $pager);
            }
        }

        if ( isset($search_filter['load']) && $search_filter['load'] == 1) {

            $data = _model('screen_auto_start')->getList($filter);

            if ( !$data )  return '暂无数据';

            foreach ($data as $k => $v) {
                $info[$k]['business_hall'] = screen_helper::by_id_get_field($v['business_hall_id'],'business_hall', 'title');
                $info[$k]['user_number']   = "\t".screen_helper::by_id_get_field($v['business_hall_id'],'business_hall', 'user_number');
                $info[$k]['unique']        = "\t".$v['device_unique_id'];
                $info[$k]['operat_time']   = $v['operate_time'];
                $info[$k]['auto_start']    = screen_auto_config::$auto_type[$v['auto_start']];
                $info[$k]['add_time']      = $v['add_time'];
            }

            $params['filename'] = '自启动记录下载';
            $params['data']     = $info;
            $params['head']     = array('营业厅', '渠道码','唯一标识' , '操作时间', '状态', '添加时间');

            Csv::getCvsObj($params)->export();
        }

        Response::assign('count' , $count);
        Response::assign('type' , $type);
        Response::assign('page' , $page);
        Response::assign('list', $list);
        Response::assign('module', '自启动');
        Response::assign('action', '记录');

        Response::assign('search_filter' , $search_filter);
        Response::display("admin/record_list.html");
    }
    
}