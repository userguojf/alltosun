<?php
/**
 * alltosun.com pc版统计 pc_stat.php
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
 * 加载performance接口
 */
probe_stat_helper::load('interface', 'performance');

/**
 * pc版探针统计
 *
 * @author wangl
 */
class pc_stat extends probe implements probe_stat
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
     * 天统计
     *
     * !CodeTemplates.overridecomment.nonjd!
     * @see stat::day_stat()
     *
     * @param   Array   参数
     */
    public function day_stat($param = array())
    {
        if ( empty($param['dates']) || empty($param['devs']) || empty($param['b_id']) ) {
            return array();
        }

        $db = $this->get_db(array(
            'b_id'  =>  $param['b_id'],
            'type'  =>  'day'
        ));

        if ( !$db ) {
            return array();
        }

        $rules    = $this->get_rule($param['b_id']);
        $dates    = $param['dates'];
        $devs     = $param['devs'];
        $data     = array(
            'devs'  =>  array(),
            'all'   =>  array(),
            'sum'   =>  array('indoor' => 0, 'outdoor' => 0)
        );

        // 初始化
        foreach ($dates as $k => $day) {
            foreach ($devs as $dev => $rssi) {
                $data['devs'][$dev]['indoor'][$day]  = array();
                $data['devs'][$dev]['outdoor'][$day] = array();
            }
            $data['all']['indoor'][$day]  = array();
            $data['all']['outdoor'][$day] = array();
        }

        foreach ($dates as $k => $day) {
            $filter = array(
                'date'  =>  $day,
                'b_id'  =>  $param['b_id']
            );
            $list = $db -> getList($filter, ' ORDER BY `id` ASC ');

            $indoor   = array();
            $outdoor  = array();
            $dev_macs = array();

            foreach ($devs as $dev => $rssi) {
                $dev_macs[$dev]['indoor']  = array();
                $dev_macs[$dev]['outdoor'] = array();
            }

            foreach ($list as $key => $probe) {
                $dev       = $probe['dev'];
                $mac       = $probe['mac'];
                $is_indoor = false;

                // 规则：连续n天，每天驻留m小时不计
                if ( !empty($rules['continued'][1]) ) {
                    if ( $probe['continued'] >= $rules['continued'][1] ) {
                        continue;
                    }
                }

                if ( $probe['is_indoor'] ) {
                    $is_indoor = true;
                }

                // 规则：停留时长小于n分钟不算室内人数
                if ( !empty($rules['minute']) ) {
                    if ( $probe['remain_time'] < ($rules['minute'] * 60) ) {
                        $is_indoor = false;
                    }
                }

                // 室内人数
                if ( $is_indoor ) {
                    if ( isset($dev_macs[$dev]['outdoor'][$mac]) ) {
                        unset($dev_macs[$dev]['outdoor'][$mac]);
                    }
                    $dev_macs[$dev]['indoor'][$mac] = 0;

                    if ( isset($outdoor[$mac]) ) {
                       unset($outdoor[$mac]);
                    }
                    $indoor[$mac] = 0;
                // 室外人数
                } else {
                    if ( !isset($indoor[$mac]) ) {
                        $outdoor[$mac] = 0;
                    }
                    if ( !isset($dev_macs[$dev]['indoor'][$mac]) ) {
                        $dev_macs[$dev]['outdoor'][$mac] = 0;
                    }
                }
            }

            foreach ($devs as $dev => $val) {
                // 当前天当前设备探测到的室内人数
                $data['devs'][$dev]['indoor'][$day]  = count($dev_macs[$dev]['indoor']);
                // 当前天当前设备探测到的室内人数
                $data['devs'][$dev]['outdoor'][$day] = count($dev_macs[$dev]['outdoor']);
            }

            // 当前天探测到的总室内人数
            $data['all']['indoor'][$day]  = count($indoor);
            // 当前天探测到的总室外人数
            $data['all']['outdoor'][$day] = count($outdoor);

            $data['sum']['indoor']  += $data['all']['indoor'][$day];
            $data['sum']['outdoor'] += $data['all']['outdoor'][$day];
        }

        return $data;
    }

    /**
     * 小时统计
     *
     * !CodeTemplates.overridecomment.nonjd!
     * @see stat::hour_stat()
     *
     * @param   Array   参数
     */
    public function hour_stat($param = array())
    {
        if ( !$param ) {
            return array();
        }

        if ( !isset($param['date']) || !$param['date'] ) {
            return array();
        }

        if ( !isset($param['b_id']) || !$param['b_id'] ) {
            return array();
        }

        if ( !isset($param['devs']) || !$param['devs'] ) {
            return array();
        }

        if ( !isset($param['hours']) || !$param['hours'] ) {
            return array();
        }

        $rules= $this->get_rule($param['b_id']);
        $date = str_replace('-', '', $param['date']);
        $b_id = $param['b_id'];
        $devs = $param['devs'];
        $hours= $param['hours'];

        $data = array(
            'devs'  =>  array(),
            'all'   =>  array(),
            'sum'   =>  array('indoor' => array(), 'outdoor' => array())
        );

        $db = $this->get_db(array(
            'b_id'  =>  $param['b_id'],
            'type'  =>  'hour'
        ));

        if ( !$db ) {
            return array();
        }

        $sql  = "SELECT * FROM `{$db -> table}` WHERE `date` = {$date} AND `b_id` = {$b_id}";
        $list = $db->getAll($sql);

        $indoor   = array();
        $outdoor  = array();
        $dev_macs = array();
        $all_masc = array();
        foreach ($hours as $h) {
            foreach ($devs as $k => $v) {
                $dev_macs[$k]['indoor'][$h]  = array();
                $dev_macs[$k]['outdoor'][$h] = array();
            }
            $all_masc['indoor'][$h] = array();
            $all_masc['outdoor'][$h]= array();
        }

        foreach ($list as $k => $v) {
            $dev       = $v['dev'];
            $is_indoor = false;
            $mac       = $v['mac'];

            // 规则：连续n天，停留m小时的人过滤掉
            if ( isset($rules['continued'][1]) ) {
                if ( $v['continued'] >= $rules['continued'][1] ) {
                    continue;
                }
            }

            if ( $v['is_indoor'] ) {
                $is_indoor = true;
            }

            // 规则：停留时长小于n分钟不算室内人数
            if ( !empty($rules['minute']) ) {
                if ( $v['remain_time'] < ($rules['minute'] * 60) ) {
                    $is_indoor = false;
                }
            }

            // 当前数据在哪个小时段
            $h = date('H', $v['frist_time']);

            if ( $is_indoor ) {
                if ( isset($outdoor[$mac]) ) {
                    unset($outdoor[$mac]);
                }
                $indoor[$mac] = 0;

                if ( isset($dev_macs[$dev]['outdoor'][$h][$mac]) ) {
                    unset($dev_macs[$dev]['outdoor'][$h][$mac]);
                    unset($all_masc['outdoor'][$h][$mac]);
                }
                $dev_macs[$dev]['indoor'][$h][$mac] = 0;
                $all_masc['indoor'][$h][$mac] = 0;
            } else {
                if ( !isset($indoor[$mac]) ) {
                    $outdoor[$mac] = 0;
                }

                if ( !isset($dev_macs[$dev]['indoor'][$h][$mac]) ) {
                    $dev_macs[$dev]['outdoor'][$h][$mac] = 0;
                    $all_masc['outdoor'][$h][$mac] = 0;
                }
            }
        }

        foreach ($hours as $h) {
            foreach ($devs as $k => $v) {
                $data['devs'][$k]['indoor'][$h]  = count($dev_macs[$k]['indoor'][$h]);
                $data['devs'][$k]['outdoor'][$h] = count($dev_macs[$k]['outdoor'][$h]);
            }
            $data['all']['indoor'][$h] = count($all_masc['indoor'][$h]);
            $data['all']['outdoor'][$h] = count($all_masc['outdoor'][$h]);
        }

        $data['sum']['indoor']  = count($indoor);
        $data['sum']['outdoor'] = count($outdoor);

        return $data;
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
        if ( !isset($param['date']) || !$param['date'] ) {
            return array();
        }

        if ( !isset($param['b_id']) || !$param['b_id'] ) {
            return array();
        }

        $db = $this->get_db(array(
                'b_id'  =>  $param['b_id'],
                'type'  =>  'day'
        ));

        if ( !$db ) {
            return array();
        }

        $page       = isset($param['page']) ? $param['page'] : 1;
        $per_page   = isset($param['per_page']) ? $param['per_page'] : 20;

        // 取营业厅规则
        $rules = $this->get_rule($param['b_id']);

        if ( isset($param['mac']) && $param['mac'] ) {
            $where = "WHERE `mac` = '{$param['mac']}' AND `date` = {$param['date']} AND `b_id` = {$param['b_id']}";
        } else {
            $where = "WHERE `date` = {$param['date']} AND `b_id` = {$param['b_id']} ";

            // 过滤掉连续n天，停留时长在m小时的人
            if ( isset($rules['continued'][1]) ) {
                $where .= " AND `continued` < {$rules['continued'][1]} ";
            }
        }

        if ( isset($param['dev']) && $param['dev'] ) {
            $where .= " AND `dev` = '{$param['dev']}'";
        }

        if ( isset($param['is_indoor']) && $param['is_indoor'] ) {
            $where .= " AND `is_indoor` = 1 ";

            // 规则：停留时长小于n分钟不算室内人数
            if ( !empty($rules['minute']) ) {
                $sec = $rules['minute'] * 60;

                $where .= " AND remain_time >= {$sec} ";
            }
        } else {
            // 规则：停留时长小于n分钟不算室内人数
            if ( !empty($rules['minute']) ) {
                $sec = $rules['minute'] * 60;

                $where .= " AND (`is_indoor` = 0 OR (`is_indoor` = 1 AND `remain_time` < {$sec})) ";
            } else {
                $where .= " AND `is_indoor` = 0 ";
            }
        }

        if ( isset($param['remain']) && $param['remain'] ) {
            $where .= " AND `remain_time` >= {$param['remain']} ";
        }

        $where .= " GROUP BY `mac` ";
        $count  = "SELECT COUNT(id) FROM `{$db->table}` {$where}";

        $order  = " ORDER BY `frist_time` ASC  ";
        $sql    = "SELECT * FROM `{$db->table}` {$where} {$order}";

        if ( isset($param['is_export']) && $param['is_export'] ) {

        } else {
            $limit  = 'LIMIT '.($page - 1) * $per_page.','.$per_page;
            $sql   .= $limit;
        }

        if ( Request::Get('debug', 0) == 1 ) {
            an_dump($sql, $count);
        }

        $list   = array();
        $count  = $db->getAll($count);
        $count  = count($count);

        if ( $count ) {
            $list = $db->getAll($sql);
        }

        return array('count' => $count, 'list' => $list);
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
        if ( !isset($param['date']) || !$param['date'] ) {
            return array();
        }

        if ( !isset($param['b_id']) || !$param['b_id'] ) {
            return array();
        }

        if ( Request::Get('debug', 0) == 1 ) {
            an_dump($param);
        }

        $db = $this->get_db(array(
                'b_id'  =>  $param['b_id'],
                'type'  =>  'hour'
        ));

        if ( !$db ) {
            return array();
        }

        $page       = isset($param['page']) ? $param['page'] : 1;
        $per_page   = isset($param['per_page']) ? $param['per_page'] : 20;

        // 取营业厅规则
        $rules = $this->get_rule($param['b_id']);

        if ( isset($param['mac']) && $param['mac'] ) {
            $where = "WHERE `mac` = '{$param['mac']}' AND `date` = {$param['date']} AND `b_id` = {$param['b_id']}";
        } else {
            $where = "WHERE `date` = {$param['date']} AND `b_id` = {$param['b_id']} ";

            // 过滤掉连续n天，停留时长在m小时的人
            if ( isset($rules['continued'][1]) ) {
                $where .= " AND `continued` < {$rules['continued'][1]} ";
            }
        }

        if ( isset($param['hour']) && $param['hour'] !== '' ) {
            $start= strtotime($param['date'].$param['hour'].'0000');
            $end  = strtotime($param['date'].$param['hour'].'5959');
            // an_dump($param['date'].$param['hour'].'0000');
            $where .= " AND `frist_time` >= {$start} AND `frist_time` <= {$end} ";
        }

        if ( isset($param['dev']) && $param['dev'] ) {
            $where .= " AND `dev` = '{$param['dev']}'";
        }

        if ( isset($param['is_indoor']) && $param['is_indoor'] ) {
            $where .= " AND `is_indoor` = 1 ";

            // 规则：停留时长小于n分钟不算室内人数
            if ( !empty($rules['minute']) ) {
                $sec = $rules['minute'] * 60;

                $where .= " AND `remain_time` >= {$sec} ";
            }
        } else {
            // 规则：停留时长小于n分钟不算室内人数
            if ( !empty($rules['minute']) ) {
                $sec = $rules['minute'] * 60;

                $where .= " AND (`is_indoor` = 0 OR (`is_indoor` = 1 AND `remain_time` < {$sec})) ";
            } else {
                $where .= " AND `is_indoor` = 0 ";
            }
        }

        if ( isset($param['remain']) && $param['remain'] ) {
            $where .= " AND `remain_time` > {$param['remain']} ";
        }

        $where .= " GROUP BY `mac` ";
        $count  = "SELECT COUNT(id) FROM `{$db -> table}` {$where}";

        $order  = " ORDER BY `frist_time` ASC  ";
        $sql    = "SELECT * FROM `{$db -> table}` {$where} {$order}";

        if ( isset($param['is_export']) && $param['is_export'] ) {

        } else {
            $limit  = 'LIMIT '.($page - 1) * $per_page.','.$per_page;
            $sql   .= $limit;
        }

        if ( Request::Get('debug', 0) == 1 ) {
            an_dump($sql, $count);
        }

        $list   = array();
        $count  = $db->getAll($count);
        $count  = count($count);

        if ( $count ) {
            $list = $db->getAll($sql);
        }

        return array('count' => $count, 'list' => $list);
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
     * 设置标签
     *
     * !CodeTemplates.overridecomment.nonjd!
     * @see performance::set_label()
     *
     * @param   String  标签名
     */
    public function set_label($name)
    {
        if ( !$name ) {
            return false;
        }

        $time = time();

        if ( isset($this->label[$name]) ) {
            $this->label[$name]['end'] = $time;
        } else {
            $this->label[$name] = array(
                    'start' =>  $time,
                    'end'   =>  $time
            );
        }

        return true;
    }

    /**
     * 获取标签
     *
     * !CodeTemplates.overridecomment.nonjd!
     * @see performance::get_label()
     *
     * @param   String   标签名
     */
    public function get_label($name)
    {
        if ( !$name ) {
            return false;
        }

        if ( $name == 'all' ) {
            foreach ($this->label as $k => $v) {
                an_dump($k.'：start '.$v['start'].' end '.$v['end'].' diff '.($v['end'] - $v['start']));
            }
        } else {
            $ary = isset($this->label[$name]) ? $this->label[$name] : array();

            if ( $ary ) {
                an_dump($name.'：start '.$ary['start'].' end '.$ary['end'].' diff '.$ary['end'] - $ary['start']);
            }
        }

        return true;
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
}
?>
