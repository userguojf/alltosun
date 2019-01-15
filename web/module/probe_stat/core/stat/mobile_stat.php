<?php
/**
 * alltosun.com 移动版统计 mobile_stat.php
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
 * 加载接口
 */
probe_stat_helper::load('interface', 'probe_stat');

/**
 * 加载probe类
 */
probe_stat_helper::load('base', 'probe');

/**
 * pc版探针统计
 *
 * @author wangl
 */
class mobile_stat extends probe implements probe_stat
{
    /**
     * 对象实例
     *
     * @var Obj
     */
    private static $instance = NULL;

    /**
     * 操作数据库对象
     *
     * @var Obj
     */
    private $db = NULL;

    /**
     * 标签
     *
     * @var Array
     */
    private $label = array();

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
     * 移动版按天统计
     *
     * !CodeTemplates.overridecomment.nonjd!
     * @see stat::day_stat()
     *
     * @param   Array   参数
     */
    public function day_stat($param = array())
    {
        if ( empty($param['date']) ) {
            return array();
        }

        if ( empty($param['res_name']) ) {
            return array();
        }

        if ( !in_array($param['res_name'], array('group', 'province', 'city', 'area')) ) {
            return array();
        }

        if ( !isset($param['res_id']) ) {
            return array();
        }

        if ( empty($param['type']) ) {
            return array();
        }

        $date = str_replace('-', '', $param['date']);
        $res_name = $param['res_name'];
        $res_id   = $param['res_id'];
        $type     = $param['type'];

        $list = city_helper::get_region_list($res_name, $res_id);

        foreach ($list as $k => $v) {
            $filter = array();

            if ( $res_name == 'group' ) {
                $filter['province_id'] = $v['id'];
            } else if ( $res_name == 'province' ) {
                $filter['city_id']     = $v['id'];
            } else if ( $res_name == 'city' ) {
                $filter['area_id']     = $v['id'];
            } else if ( $res_name == 'area' ) {
                $filter['business_id'] = $v['id'];
            }

            if ( !empty($param['hour']) ) {
                $filter['date_for_hour'] = $param['hour'];
            }

            $filter['date_for_day']  = $date;

            $stat_list = _model('probe_stat_hour')->getList($filter);

            $num = 0;
            foreach ($stat_list as $val) {
                if ( $type == 1 ) {
                    $num += $val['outdoor'];
                } else if ( $type == 2 ) {
                    $num += $val['indoor'];
                } else if ( $type == 3 ) {
                    $num += $val['new_num'];
                } else if ( $type == 4 ) {
                    $num += $val['old_num'];
                } else {
                    return '无法识别的类型';
                }
            }

            if ( $num ) {
                $list[$k]['num'] = $num;
            } else {
                unset($list[$k]);
            }
        }

        return $list;
    }

    /**
     * 移动版小时统计
     *
     * !CodeTemplates.overridecomment.nonjd!
     * @see stat::hour_stat()
     *
     * @param   Array   参数
     */
    public function hour_stat($param = array())
    {

    }

    /**
     * 天列表
     *
     * !CodeTemplates.overridecomment.nonjd!
     * @see stat::day_list()
     *
     * @param   Array   参数
     */
    public function day_list($param = array())
    {
        $pc_stat = probe_stat_helper::instance('stat', 'pc_stat');

        if ( $param['type'] == 1 ) {
            $param['is_indoor'] = 1;
        } else if ( $param['type'] == 2 ) {
            $param['is_indoor'] = 0;
        } else {
            return array();
        }

        return $pc_stat -> day_list($param);
    }

    /**
     * 小时列表
     *
     * !CodeTemplates.overridecomment.nonjd!
     * @see stat::hour_list()
     *
     * @param   Array   参数
     */
    public function hour_list($param = array())
    {
        $pc_stat = probe_stat_helper::instance('stat', 'pc_stat');

        if ( $param['type'] == 1 ) {
            $param['is_indoor'] = 1;
        } else if ( $param['type'] == 2 ) {
            $param['is_indoor'] = 0;
        } else {
            return array();
        }

        return $pc_stat -> hour_list($param);
    }

    /**
     * 设备品牌分布
     *
     * !CodeTemplates.overridecomment.nonjd!
     * @see stat::brand_stat()
     *
     * @param   Array   参数
     */
    public function brand_stat($param = array())
    {
        return array();
    }

    /**
     * !CodeTemplates.overridecomment.nonjd!
     * @see probe_stat::new_customer_stat()
     */
    public function new_customer_stat($param = array())
    {

    }

    /**
     * !CodeTemplates.overridecomment.nonjd!
     * @see probe_stat::old_customer_stat()
     */
    public function old_customer_stat($param = array())
    {

    }

    public function week_for_hour($res_name, $res_id, $dates)
    {
        if ( !$res_name || !$dates ) {
            return array();
        }

        $data = array();

        foreach ($dates as $day) {
            if ( $res_name == 'group' ) {
                $filter = array(
                    'date_for_day'  =>  $day,
                );
            } else if ( $res_name == 'province' ) {
                $filter = array(
                    'province_id'   =>  $res_id,
                    'date_for_day'  =>  $day,
                );
            } else if ( $res_name == 'city' ) {
                $filter = array(
                    'city_id'   =>  $res_id,
                    'date_for_day'  =>  $day,
                );
            } else if ( $res_name == 'area' ) {
                $filter = array(
                    'area_id'   =>  $res_id,
                    'date_for_day'  =>  $day,
                );
            } else if ( $res_name == 'business_hall' ) {
                $filter = array(
                    'business_id'   =>  $res_id,
                    'date_for_day'  =>  $day,
                );
            }

            $list   = _model('probe_stat_day')->getList($filter);
            $indoor = 0;

            foreach ($list as $k => $v) {
                $indoor += $v['indoor'];
            }
            $data[$day] = $indoor;
        }

        return $data;
    }
}
?>