<?php
/**
 * alltosun.com  probe.php
 * ============================================================================
 * 版权所有 (C) 2009-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王磊 (wangl@alltosun.com) $
 * $Date: 2017-5-31 下午2:55:35 $
*/

/**
 * 加载db接口
 */
probe_stat_helper::load('interface', 'probe_db');

/**
 * 加载cache接口
 */
probe_stat_helper::load('interface', 'probe_cache');

/**
 * 加载规则接口
 */
probe_stat_helper::load('interface', 'probe_rule');

/**
 * probe类
 *
 * @author wangl
 */
class probe implements probe_db, probe_cache, probe_rule
{
    /**
     * 对象实例
     *
     * @var Obj
     */
    private static $instance = NULL;

    /**
     * 数据库操作对象
     *
     * @var Array
     */
    private static $dbs = array();

    /**
     * 获取对象实例
     *
     * @return  Obj
     */
    public static function instance()
    {
        if ( self::$instance === NULL ) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * 构造函数
     *
     * @return  Obj
     */
    private function __construct()
    {

    }

    /**
     * 获取数据库操作对象
     *
     * !CodeTemplates.overridecomment.nonjd!
     * @see probe_db::get_db()
     */
    public function get_db($param = array())
    {
        if ( !isset($param['b_id']) || !isset($param['type']) ) {
            return false;
        }

        $b_id = $param['b_id'];
        $type = $param['type'];

        // 获取营业厅信息
        $b_info = business_hall_helper::get_business_hall_info($b_id);

        if ( !$b_info ) {
            return false;
        }

        if ( $type != 'hour' && $type != 'day' ) {
            return false;
        }

        $p_id  = $b_info['province_id'];
        $c_id  = $b_info['city_id'];
        $a_id  = $b_info['area_id'];
        $b_id  = $b_id;
        $table = 'probe_'.$p_id.'_'.$c_id.'_'.$a_id.'_'.$b_id.'_'.$type;

        $citys    = probe_config::$citys;

        $database = isset($citys[$p_id]) ? $citys[$p_id] : '';
        if ( is_array($database) ) {
            $database = isset($database[$c_id]) ? $database[$c_id] : $database['default'];
        }
        if ( is_array($database) ) {
            $database = isset($database[$this->a_id]) ? $database[$this->a_id] : $database['default'];
        }
        if ( !$database || is_array($database) ) {
            return false;
        }

        if ( !empty($param['create']) ) {
            $db = _model('init', $database);
            $db -> table = $table;
        } else {
            $db = _model($table, $database);
        }

        if ( !$db ) {
            return false;
        }

        return $db;
/*
        if ( empty(self::$dbs[$b_id]) ) {

            $db = _model('probe')->probe_init($b_id, $type);

            if ( !$db ) {
                return false;
            }

            self::$dbs[$b_id] = $db;
        }

        return self::$dbs[$b_id];
*/
    }

    /**
     * 设置缓存
     *
     * !CodeTemplates.overridecomment.nonjd!
     * @see probe_cache::set_cache()
     */
    public function set_cache($param = array())
    {
        return array();
    }

    /**
     * 获取数据缓存
     *
     * !CodeTemplates.overridecomment.nonjd!
     * @see probe_cache::get_cache()
     */
    public function get_cache($param = array())
    {

    }

    /**
     * 获取营业厅规则
     *
     * !CodeTemplates.overridecomment.nonjd!
     * @see probe_rule::get_rule()
     *
     * @param   Int 营业厅ID
     */
    public function get_rule($b_id)
    {
        if ( !$b_id ) {
            return array();
        }

        $data = array();
        $list = _model('probe_business_rule')->getList(array('business_id'=>$b_id, 'status'=>1));

        foreach ($list as $k => $v) {
            if ( !$v['alias'] || !$v['value'] ) {
                continue;
            }

            $key        = $v['alias'];
            $value      = explode('-', $v['value']);

            if ( isset($value[1]) ) {
                $data[$key] = $value;
            } else {
                $data[$key] = (string)$value[0];
            }
        }

        return $data;
    }
}
?>