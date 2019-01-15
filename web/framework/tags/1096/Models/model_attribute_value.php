<?php

/**
 * alltosun.com 资源属性值模型 attribute_value.php
 * ============================================================================
 * 版权所有 (C) 2007-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2010-2-15 上午 20:37:26 $
 * $Id: model_attribute_value.php 477 2012-09-10 16:57:32Z anr $
*/

/**
 * 资源属性值模型
 * @author anr@alltosun.com
 * @package AnModel
 */
class model_attribute_value extends Model
{
    public $table = 'attribute_value';
    public $cache = 0;
    public $tb = array();

    /**
     * 类初始化后钩子，如果表不存在自动建表
     */
    public function createTable()
    {
        _model('table')->exec("
CREATE TABLE IF NOT EXISTS `{$this->table}` (
`res_id` int(10) unsigned NOT NULL,
`res_type` varchar(100) NOT NULL DEFAULT '0' COMMENT '资源类型',
`attribute_id` int(10) unsigned NOT NULL COMMENT '属性id',
`value` text NOT NULL COMMENT '属性值',
UNIQUE KEY `res_id` (`res_id`,`res_type`,`attribute_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='属性值';");
    }

    /**
     * 返回 res_type,res_id 对应的指定属性
     * @param array $option array('res_id'=>$id, 'res_type'=>$res_type_main, 'attribute_id'=>3)
     * @return array 1维数组
     * @author anr@alltosun.com
     */
    public function get_value($option)
    {
        $v = $this->__call('read', array($option));
        return $this->treat_val($v['attribute_id'], $v['value']);
    }

    /**
     * 返回 res_type,res_id 对应的所有属性
     * @param array $option array('res_id'=>$id, 'res_type'=>$res_type_main)
     * @return array 2维数组
     * @author anr@alltosun.com
     */
    public function get_list($option)
    {
        $tem_info_arr = $this->__call('getList', array($option));

        $main_arr = array();
        foreach ($tem_info_arr as $v) {
            $main_arr[$v['attribute_id']] = $this->treat_val($v['attribute_id'], $v['value']);
        }

        return $main_arr;
    }

    /**
     * 根据扩展属性类型对内容处理
     * @param $attribute_id 扩展属性id
     * @param $v    内容
     * @author anr@alltosun.com
     */
    private function treat_val($attribute_id, $v)
    {
        $arrtibute_type = _uri('attribute', $attribute_id, 'type');
        if ($arrtibute_type == 'select' || $arrtibute_type == 'radio' || $arrtibute_type == 'checkbox') {
            // 将原是数组的属性恢复成数组
            return explode("\n", str_replace("\r", '', $v));
        } else {
            return $v;
        }
    }

    /**
     * 写入扩展属性表
     * @param $id
     * @param $res_type
     * @param $k
     * @param $v
     * @author anr@alltosun.com
     */
    public function replace($id, $res_type, $k, $v)
    {
        if (is_array($v)) $v = implode("\n", $this->strip_br($v));
        $info = array(
           'res_id'       => $id,
           'res_type'     => $res_type,
           'attribute_id' => $k,
           'value'        => $v
        );
        return $this->__call('create', array($info, 'REPLACE'));
    }

    /**
     * 将数组更新到 attribute_value 表中
     * @param $option 条件数组 array('res_id'=>68, 'res_type'=>1)
     * @param $array  更新的内容，可是1维数组，也可以是直接post过来的2维数组
     *  或 array(4=>'ok')
     *  或 array(array(4=>'ok'), array(10=>'green')) 直接post过来的2维数组
     * @example replace_array(array('res_id'=>68, 'res_type'=>1), array(4=>'ok'))
     * @author anr@alltosun.com
     */
    public function replace_array($option, $array)
    {
        if (count($array) > 1) {
            // 如果是2维数组 array(array(4=>'ok'), array(10=>'green'));
            foreach ($array as $k=>$v) {
                $this->replace($option['res_id'], $option['res_type'], $k, $v);
            }
        } else {
            list($k, $v) = each($array);
            $this->replace($option['res_id'], $option['res_type'], $k, $v);
        }
    }

    /**
     * 换行转换
     */
    private function strip_br($str, $replace='')
    {
        if (is_array($str)) {
            foreach ($str as $k=>$v) {
                $str[$k] = $this->strip_br($v, $replace);
            }
            return $str;
        } else {
            $order   = array("\r\n", "\n", "\r");
            return str_replace($order, $replace, $str);
        }
    }
}
?>