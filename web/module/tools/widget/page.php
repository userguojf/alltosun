<?php
/**
 * alltosun.com  page.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王德康 (wangdk@alltosun.com) $
 * $Date: 2014-4-14 下午8:49:09 $
 * $Id$
 */

class page_widget {


    /**
     * 获取列表支持分页
     * @param 表名 $table_name
     * @param 页码 $page_no
     * @param 条件 $filter
     * @param 排序条件 $order
     * @param 小分页页码(比如瀑布流，加载三次更多) $start_num
     * @param 每页多少条 $per_count
     * @param 小页码每页多少条 $per_num_page
     * @param 是否进行小分页 $is_per_num
     * @param 分页链接 $url
     * @return array()
     */
    public function get_res_list($table_name, $page_no, $filter, $order, $start_num = 1, $per_count = 3, $per_num_page = 1, $is_per_num = 1)
    {

        $list = $page_ajax_array = array();
        $is_more = 1;

        $count = _model($table_name)->getTotal($filter);

        if ($count) {
            $pager = new Pager($per_count);

            if ($pager->generate($count)) {
                Response::assign('pager', $pager);
            }

            $list = _model($table_name)->getList($filter, ' '.$order.' '.$pager->getLimit($page_no));

            // 是否要求小分页
            if ($is_per_num) {
                $pages_num = (int)ceil(count($list)/$per_num_page);
                if ($pages_num <= $start_num) $is_more = 0;  // 是否显示加载更多
                $list = array_slice($list, $per_num_page *($start_num - 1), $per_num_page);
            }

            if ($pager) {
                $page_ajax_array = _widget('tools.page')->get_ajax_pages($pager, $page_no);
            }

        }


        $data = array(
                'info'       =>'ok',
                'list'       => $list,
                'is_more'    => $is_more,
                'page'       => $page_ajax_array,
                'is_per_num' => $is_per_num,
                 'count'     => $count
        );

        return $data;
    }

    /**
     * 为ajax组合分页
     * @param object $pager
     * @return multitype:boolean Ambigous <multitype:, unknown>
     */
    public function get_ajax_pages($pager, $page_no)
    {
        $pages_urls = $pages_nums = $pages_array = array();

        foreach ($pager->getPagesArray(5) as $v) {
            $pages_nums[] = $v;
            $pages_urls[] = $pager->link($v);
        }

        return array(
                        'first_url'=> $pager->begin(),
                        'prev_url' => $pager->prev(),
                        'next_url' => $pager->next(),
                        'last_url' => $pager->end(),
                        'pages_nums' => $pages_nums,
                        'current_page' => $page_no,
                        'last_page'    => $pager->end(),

                        'url_first_url'=> $pager->link($pager->begin()),
                        'url_prev_url' => $pager->link($pager->prev()),
                        'url_next_url' => $pager->link($pager->next()),
                        'url_last_url' => $pager->link($pager->end()),
                        'url_pages_urls' => $pages_urls,
                        'url_last_page'    => $pager->link($pager->end())
                   );
    }
}
?>