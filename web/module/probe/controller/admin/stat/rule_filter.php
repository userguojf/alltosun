<?php

/**
 * 展示被规则过滤掉的mac地址
 *
 * @author  wangl
 */

// load func.php
probe_helper::load('func');

class Action
{
    /**
     * 每页展示多少条
     *
     * @var Int
     */
    private $per_page = 20;

    /**
     * 列表页
     *
     * @return  String
     */
    public function index()
    {
        $b_id  = Request::Get('b_id', 0);
        $date  = Request::Get('date', '');
        $rule  = Request::Get('rule', 0);
        $page  = Request::Get('page_no', 1);
        $debug = Request::Get('debug', 0);

        // 取有探针的营业厅ids
        $b_ids = probe_dev_helper::get_business_ids('group', 0);

        if ( !$b_ids ) {
            return '没有探针设备';
        } else {
            if ( $b_id ) {
                if ( !in_array($b_id, $b_ids) ) {
                    return '营业厅下没有设备';
                }
            } else {
                $b_id = $b_ids[0];
            }
        }

        // 营业厅信息
        $b_info = business_hall_helper::get_business_hall_info($b_id);

        if ( !$b_info ) {
            return '营业厅不存在';
        }

        if ( !$date ) {
            $date = date('Y-m-d');
        }

        $int_date = str_replace('-', '', $date);

        $filter = array(
            'business_id'   =>  $b_id,
            'alias'         =>  $rule == 0 ? 'continued' : 'minute'
        );

        // 规则信息
        $rule_info = _model('probe_business_rule')->read($filter);

        $db    = get_db($b_id);

        if ( $rule_info ) {
            $where = " WHERE `date` = {$int_date} ";

            if ( $rule == 0 ) {
                $ary    = explode('-', $rule_info['value']);

                $where .= " AND `continued` >= {$ary[1]}";
            } else {
                $num    = $rule_info['value'] * 60;

                $where .= " AND `is_indoor` = 1 AND `remain_time` < {$num}";
            }
            $sql   = " SELECT `id` FROM `{$db -> table}` {$where} GROUP BY `mac` ";

            $list  = $db -> getAll($sql);

            $count = count($list);

            if ( $count ) {
                $pager = new Pager($this -> per_page);

                if ($pager->generate($count)) {
                    Response::assign('pager', $pager);
                }

                $limit = ' LIMIT '.($page - 1) * $this -> per_page.','.$this -> per_page;
                $sql   = " SELECT * FROM `{$db -> table}` {$where} GROUP BY `mac` {$limit}";

                if ( $debug ) {
                    an_dump($sql);
                }

                $list  = $db -> getAll($sql);
            } else {
                $list = array();
            }
        } else {
            $list  = array();
            $count = 0;
        }

        Response::assign('list', $list);
        Response::assign('count', $count);
        Response::assign('rule', $rule);
        Response::assign('b_ids', $b_ids);
        Response::assign('b_info', $b_info);
        Response::assign('date', $date);
        Response::display('admin/stat/rule_filter/index.html');
    }
}
