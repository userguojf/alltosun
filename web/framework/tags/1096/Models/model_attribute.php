<?php

/**
 * alltosun.com 扩展属性模型 attribute.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2010-1-5 下午12:26:26 $
 * $Id: model_attribute.php 477 2012-09-10 16:57:32Z anr $
*/

/**
 * 扩展属性模型
 * @author anr@alltosun.com
 * @package AnModel
 */
class model_attribute extends ModelRes
{
    public $table = 'attribute';
    public $pk = 'id';
    public $tb = array();

    /**
     * 类初始化后钩子，如果表不存在自动建表
     */
    public function createTable()
    {
        _model('table')->exec("
CREATE TABLE IF NOT EXISTS `{$this->table}` (
`id` int(11) NOT NULL AUTO_INCREMENT COMMENT '属性id',
`type` enum('input','radio','checkbox','select','textarea','file','date') NOT NULL DEFAULT 'input' COMMENT '属性类型',
`is_system` tinyint(1) unsigned NOT NULL DEFAULT '0' COMMENT '是否是系统定义的属性（后台不能删除）',
`value` text NOT NULL COMMENT '属性可选值/默认值',
PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='属性表' AUTO_INCREMENT=1100 ;");
    }

    /**
     * 重载read，按属性不同进行内容处理
     * @see ModelRes::read()
     */
    public function read($filter, $order = '')
    {
        $tem = parent::read($filter);

        if (!$tem) {
            return array();
        }

        // 多取
        if (isset($tem[0]) && is_array($tem[0])) {
            foreach ($tem as $k=>$v) {
                $type = $tem[$k]['type'];
                // 将指定属性的默认值分拆成数组
                if ($type == 'select' || $type == 'radio' || $type == 'checkbox') {
                    $tem[$k]['value'] = explode("\n", str_replace("\r", '', $tem[$k]['value']));
                }
            }
        }

        $type = $tem['type'];
        // 将指定属性的默认值分拆成数组
        if ($type == 'select' || $type == 'radio' || $type == 'checkbox') {
            $tem['value'] = explode("\n", str_replace("\r", '', $tem['value']));
        }
        return $tem;
    }
}
?>