<?php
/**
 * alltosun.com  title.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-1-22 下午3:12:37 $
 * $Id$
 */
class Action
{
    public function __call($action = '', $param = array())
    {
        if ( $action == 'index' ) return '方法名为渠道码';

        $info = _model('business_hall')->read(array('user_number' => $action));

        if ( !$info ) return '营业厅不存在';

        p($info);
    }
}