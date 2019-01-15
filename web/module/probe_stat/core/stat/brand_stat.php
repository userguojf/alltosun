<?php
/**
 * alltosun.com 设备品牌统计 brand_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王磊 (wangl@alltosun.com) $
 * $Date: 2017-5-31 下午12:40:17 $
*/

/**
 * 加载probe类
 */
probe_stat_helper::load('base', 'probe');

/**
 * 加载错误处理
 */
// probe_stat_helper::instance('base', 'probe_error');

/**
 * pc版探针统计
 *
 * @author wangl
 */
class brand_stat extends probe
{
    /**
     * 对象实例
     *
     * @var Obj
     */
    private static $instance = NULL;

    /**
     * 营业厅ID
     *
     * @var Int
     */
    private $b_id = 0;

    /**
     * 日期
     *
     * @var Int
     */
    private $date = 0;

    /**
     * 构造函数
     *
     * @return  Obj
    */
    private function __construct()
    {

    }

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
     * 设置
     *
     * @param   Array   参数
     */
    public function set_param($param = array())
    {
        if ( empty($param['b_id']) ) {
            trigger_error('brand_stat|show|your must be give a business id');
        }

        $this->b_id = $param['b_id'];

        if ( empty($param['date']) ) {
            $this->date = (int)date('Ymd');
        } else {
            $this->date = (int)str_replace('-', '', $param['date']);
        }

        return self::$instance;
    }

    /**
     * 获取数据
     *
     * @param   Array   参数
     */
    public function get_data($param = array())
    {
        $devs  = probe_stat_helper::get_devs($this->b_id);

        if ( !$devs ) {
            trigger_error('brand_stat|show|营业厅下没有探针设备');
        }

        $db = $this->get_db(array('type' => 'day', 'b_id' => $this->b_id));

        if ( !$db ) {
            trigger_error('db return false');
        }

        $filter = array(
            'date'      =>  $this -> date,
            'b_id'      =>  $this -> b_id,
            'is_indoor' =>  1
        );
        $list   = $db -> getList($filter, ' GROUP BY `mac` ');

        $brands = probe_stat_helper::get_brands();

        foreach ($list as $k => $v) {
            $name = probe_stat_helper::get_brand($v['mac']);

            if ( isset($brands[$name]) ) {
                $brands[$name] ++;
            } else {
                $brands['其他'] ++;
            }
        }

        foreach ($brands as $k => $v) {
            if ( !$v ) {
                unset($brands[$k]);
            }
        }

        $return = array(
            'brand' =>  $brands
        );

        if ( !empty($param['list']) ) {
            $return['list'] = $list;
        }

        return $return;
    }
}
?>