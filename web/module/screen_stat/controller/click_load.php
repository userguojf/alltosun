<?php
/**
 * alltosun.com  click_load.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-2-24 上午10:34:32 $
 * $Id$
 */

class Action
{
    public function index()
    {
        $filter['day >='] = 20180101;
        $filter['day <='] = 20180131;

        $filter  = get_mongodb_filter($filter);
// p($filter);
        $num = _mongo('screen', 'screen_click_record')->count($filter);
p($num);
    }
}