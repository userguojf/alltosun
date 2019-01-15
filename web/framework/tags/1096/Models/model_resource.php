<?php

/**
 * alltosun.com resource表操作类 model_resouce.php
 * ============================================================================
 * 版权所有 (C) 2007-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 安然 (anr@alltosun.com) $
 * $Date: 2010-2-15 下午04:33:30 $
 * $Id: model_resource.php 977 2015-01-22 11:37:09Z anr $
*/

/**
 * resource表操作类
 * @author anr@alltosun.com
 * @package AnModel
 */
class model_resource extends Model
{
    public $table = 'resource';
    public $db_select = 'm'; // 强制选择主库
    public $lifetime = 86400;
    public $tb = array();

    public $resourceList = array(); // 自定义的
    public $tableExists = NUll; // 表是否存在

    /**
     * 初始化，从数据库和配置中一次性加载
     */
    public function init_after()
    {
        $this->link = 1;
        $this->tableExists = _model('table')->tableExists('resource');
        if ($this->tableExists) {
            $resource_list = $this->getList(array(1=>1));
            if ($resource_list) {
                $this->buildList($resource_list);
            }
        }
//var_dump($resource_list);

        // 从配置中取，二维数据，id 可以不写，name 不可重复，以表中定义的也不可以重复
        // array(array('id'=>100123, 'name'=>'car', 'model'=>model,……), array('id'=>100124, 'name'=>'car', 'model'=>model,……)……)
        $resource_list = Config::get('resourceList');
        if ($resource_list) {
            $this->buildList($resource_list);
        }
//var_dump($resource_list);
var_dump($this);
    }

    /**
     * 重载
     * @param mixed $res_type
     * @return array()
     */
    public function read($res_type)
    {
        if (is_array($res_type)) { $res_type = $res_type['id']; }
    
        if (!empty($this->resourceList) && isset($this->resourceList[$res_type])) {
            return $this->resourceList[$res_type];
        }
        return array();
    }

    /**
     * 加载参数重新构造
     * @param array $resource_list
     * @return NULL
     */
    private function buildList($resource_list)
    {
        foreach ($resource_list as $v) {
            if (isset($v['db_op']) && $v['db_op'] && strpos($v['db_op'], ',')) {
                $v['db_op_list'] = explode(',', $v['db_op']);
            }

            if ($v['table'] && strpos($v['table'], ',')) {
                $v['table_list'] = explode(',', $v['table']);
            }

            // name 不可以是数字
            if (is_numeric($v['name'])) {
                throw new AnException('Model Error!', __METHOD__ . ' Error!name is:' . $v['name']);
            }
            // 不可以重复
            if ((isset($v['id']) && isset($this->resourceList[$v['id']])) || isset($this->resourceList[$v['name']])) {
                throw new AnException('Resource Error!', __METHOD__ . ' Error!duplicate key.Id is:' . var_export($v['id'], 1) . '.Name is:' . var_export($v['name'], 1));
            }
            // 自定义可以没有 id
            if (isset($v['id'])) {
                $this->resourceList[$v['id']] = $v;
            }
            $this->resourceList[$v['name']] = $v;
        }
    }

    /**
     * 建表
     */
    public function createTable()
    {
        _model('table')->exec("
CREATE TABLE IF NOT EXISTS `{$this->table}` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT COMMENT '资源类型res_type',
  `name` char(100) NOT NULL COMMENT '资源名称',
  `model` char(100) NOT NULL COMMENT '模块类名称',
  `route` tinyint(3) unsigned NOT NULL COMMENT '是开启model路由方式',
  `split` enum('','rule','assign') NOT NULL COMMENT '分库分表的方式，''rule'':规则（根据取余结果分配库表）；''assign'':指定（随机分配库表）',
  `table` CHAR( 255 ) NOT NULL COMMENT '模块对应的表名称',
  `description` char(100) NOT NULL COMMENT '中文描述',
  `db_op` CHAR( 255 ) NOT NULL COMMENT '数据库配置key',
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8 COMMENT='资源定义表' AUTO_INCREMENT=100100;");
    }
}
?>