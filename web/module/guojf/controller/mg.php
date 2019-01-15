<?php
/**
 * alltosun.com  sql.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-11-4 下午5:08:21 $
 * $Id$
 */
class Action
{
    public function index()
    {
        $table   = tools_helper::Get('t', '');
        $filter1 = tools_helper::Get('filter', array());
        $order   = tools_helper::Get('o', '');
        $limit   = tools_helper::Get('l', 0);

        $filter1 = get_mongodb_filter($filter1);
        $filter2 = array();

        if ( $order ) {
            $filter2['sort'] = array("{$order}" => -1);
        }

        if ( $limit ) {
            $filter2['limit'] = $limit;
        } else {
            p('符合条件的总量:'._mongo('screen', $table)->count($filter1));
        }

// var_export($filter1);var_export($filter2);
        $res = _mongo('screen', $table)->find($filter1, $filter2);
        p('符合条件数组如下');
        p($res -> toArray());
    }
}