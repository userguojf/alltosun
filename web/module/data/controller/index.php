<?php
/**
  * alltosun.com  index.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年12月1日 下午5:33:57 $
  * $Id$
  */
class Action
{
    public function to_mongodb()
    {

        $source_table   = tools_helper::Get('source_table', '');
        $page           = tools_helper::Get('page', 1);
        $to_table       = tools_helper::Get('to_table', '');
        $to_db          = tools_helper::Get('to_db', '');

        _widget('data')->import_to_mongodb($source_table, $page, $to_table, $to_db);

    }
}