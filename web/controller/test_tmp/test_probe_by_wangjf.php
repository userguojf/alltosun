<?php
/**
  * alltosun.com  test_probe_by_wangjf.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年5月14日 下午5:06:47 $
  * $Id$
  */
probe_helper::load('stat', 'trait');
class Action
{
    use stat;
    private static $key = 'wangjf';
    public function exec()
    {
        $b_id = tools_helper::get('b_id', 0);
        $sql = tools_helper::get('sql', '');
        $key = tools_helper::Get('key', '');
        $action = tools_helper::Get('action', '');
        $handle = tools_helper::Get('handle', '');
        $type = tools_helper::Get('type', 'hour');
        $sql_filter = tools_helper::Get('sql_filter', array());

        if ($key != self::$key) {
            return '验证失败';
            exit;
        }

        $db = get_db($b_id, $type);

        if (!$sql_filter) {
            $sql_filter[1] = 1;
        }

        if (isset($sql_filter['sql'])) {
            $res =$db->getAll($sql_filter['sql']);
        } else {
            $res = $db->$action($sql_filter, $handle);
        }

        if ($is_json == 1) {
            echo json_encode($res);
        } else {
            p($res);
        }

        p($res);
    }

    public function mac_decode()
    {
        $mac = tools_helper::get('mac', '9c:da:3e:8f:c3:89');
        $mac = probe_helper::mac_decode($mac);
        p($mac);
    }

    public function mac_encode()
    {
        $mac = tools_helper::get('mac', '242600325516392');
        $mac = probe_helper::mac_encode($mac);
        p($mac);
    }

    /**
     * 获取探针活跃设备量
     */
    public function get_active_count()
    {
        $start = tools_helper::Get('start_date', '');
        $end = tools_helper::Get('end_date', '');
        if (!$start || !$end) {
            echo '开始时间或结束时间不能为空';exit;
        }

        $filter = array(
                'status' => 1,
                'date >=' => date('Ymd', strtotime($start)),
                'date <=' => date('Ymd', strtotime($end)),
        );

        $device = _model('probe_device_status_stat_day')->getFields('device', $filter, ' GROUP BY device ');
        echo count($device);
        p($device);
    }
}