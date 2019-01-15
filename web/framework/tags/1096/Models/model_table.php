<?php

/**
 * alltosun.com 表操作类 model_table.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (an@alltosun.com) $
 * $Date: 2010-02-17 下午16:33:30 $
 * $Id: model_table.php 1016 2015-02-02 19:01:53Z anr $
*/

/**
 * 表操作类
 * @author anr@alltosun.com
 * @package AnModel
 */
class model_table extends Model
{
    public $table = '';
    public $db_select = 'm'; // 强制选择主库
    public $lifetime = 86400;
    public $tb = array();
    public $tableExists = 1;
    public $table_ns = 'table_alter_ns';
    public $table_list_ns = 'table_alter_ns';
    public $table_alter_ns = 'table_alter_ns';

    /**
     * describe 的调用
     * @param string $table
     */
    public function read($table)
    {
        $this->table = $table;
        return $this->__call('describe', array($table));
    }
}
?>