<?php

/**
 * alltosun.com resource_split_assign 表操作类 model_resource_split_assign.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2012-1-11 下午18:17:30 $
 * $Id: model_resource_split_assign.php 477 2012-09-10 16:57:32Z anr $
*/

/**
 * 本表记录model_name和split_key对应的库配置名与表名
 * @author anr@alltosun.com
 * @package AnModel
 */
class model_resource_split_assign extends Model
{
    public $table = 'resource_split_assign';
    public $db_select = 'm'; // 强制选择主库
    public $lifetime = 3600;
    public $tb = array();

    /**
     * 类初始后钩子，如果表不存在自动创建
     */
    public function createTable()
    {
        _model('table')->exec("
CREATE TABLE IF NOT EXISTS `{$this->table}` (
`resource_id` int(10) unsigned NOT NULL COMMENT 'resource表中id',
`split_key` int(10) unsigned NOT NULL COMMENT '打散key',
`db_op` char(100) CHARACTER SET latin1 NOT NULL COMMENT '数据库配置名',
`table` char(100) CHARACTER SET latin1 NOT NULL COMMENT '表名',
UNIQUE KEY `model` (`split_key`,`resource_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_bin COMMENT='anphp assign 分库分表方式对应关系';");
    }
}
?>