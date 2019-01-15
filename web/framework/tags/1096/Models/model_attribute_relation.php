<?php

/**
 * alltosun.com 资源属性关联模型 model_attribute_relation.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2010-2-15 上午 8:56:26 $
 * $Id: model_attribute_relation.php 477 2012-09-10 16:57:32Z anr $
*/

/**
 * 资源属性关联模型
 * @author anr@alltosun.com
 * @package AnModel
 */
class model_attribute_relation extends Model
{
    public $table = 'attribute_relation';
    public $tb = array();
    public $cache = 0;

    /**
     * 类初始化后钩子，如果表不存在自动建表
     */
    public function createTable()
    {
        _model('table')->exec("
CREATE TABLE IF NOT EXISTS `{$this->table}` (
  `res_type` int(11) NOT NULL,
  `attribute_id` int(10) unsigned NOT NULL DEFAULT '0' COMMENT '关联的属性id',
  UNIQUE KEY `res_type` (`res_type`,`attribute_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 ROW_FORMAT=FIXED COMMENT='属性关联表';");
    }

    /**
     * 返回 res_type 对应的所有属性 id
     * @param int $res_type
     * @return array 1维数组 array(10, 50)
     * @example get_list('res_type')
     * @example get_list(array('res_type' => 20069)
     */
    public function get_list($res_type)
    {
        static $g = array();
        if (is_array($res_type)) $res_type = $res_type['res_type'];
        if (!$res_type) throw new Exception("Error Empty res_type.");
        if (isset($g[$res_type]) && is_array($g[$res_type])) { return $g[$res_type]; }

        return $g[$res_type] = $this->__call('getFields', array('attribute_id', array('res_type'=>$res_type)));
    }

    /**
     * 返回 res_type 对应所有属性列表
     * @param int $res_type
     * @return array 2维数组 array(10 => array("id"=>10,"name"=>"石材","type"=>"input","value"=>""), 11 => array("id"=>11,"name"=>"石材11","type"=>"radio","value"=>""))
     */
    public function get_all_list($res_type)
    {
        $tem_arr = $this->get_list($res_type);
        $main_arr = array();
        foreach ($tem_arr as $v) {
            $main_arr[$v] = _uri('attribute', $v);
        }
        return $main_arr;
    }
}
?>