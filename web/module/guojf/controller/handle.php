<?php
/**
 * alltosun.com  table.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-12-13 下午6:22:27 $
 * $Id$
 */
class Action
{
    public function __call( $action = '', $param = array() )
    {
        $table = tools_helper::get('t', '');
        if ( !$table ) return '没有传操作表';

        $action = tools_helper::get('action', '');
        if ( !$action ) return '想怎么操作';

        $filter = tools_helper::get('filter', array());
        if ( !$filter ) return '没有传条件';

        _model($table)->$action ($filter);

        echo 'finished';
    }
}