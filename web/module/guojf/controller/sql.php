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
        $table  = tools_helper::Get('t', '');
        $filter = tools_helper::Get('filter', array());
        $order  = tools_helper::Get('o', 'ASC');
        $field  = tools_helper::Get('f', 'id');
        $limit  = tools_helper::Get('l', 0);
        $token  = tools_helper::Get('token', '');
        $sql    = tools_helper::Get('sql', '');

        if ( $sql ) {
            $arr = _model($table)->getAll($sql);
            p($arr);
            exit();
        }

        if ($token != 'alltosun') {
            return '验证失败';
        }

        if ($limit) {
            $limit = " LIMIT {$limit} ";
        } else {
            $limit = '';
        }

        $order =  " ORDER BY `{$field}` {$order} {$limit} ";

        if (!$filter) {
            $filter = array( 1 => 1);
        }

        $res = _model($table)->getList($filter, $order);

        p($res);
    }
}