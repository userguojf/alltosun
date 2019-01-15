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
 * $Date: 2017-4-10 下午12:40:45 $
*/

// load trait
probe_helper::load('stat', 'trait');

class probe_widget
{
    use stat;

    public function corn($date = 0)
    {
        if ( $date ) {

        } else {
            $time     = strtotime('-1 day', time());
            $date     = (int)date('Ymd', $time);
        }

        $this -> widget_day_corn($date);
    }

    public function hour_corn($time='')
    {
        if ( $time ) {

        } else {
            $time = time() - 3600;
        }

        $this -> widget_hour_corn($time);
    }

    /**
     * 烽火计划任务
     *
     * @return  String
     */
    public function fenghuo()
    {
        // 营业厅：西城区城关营业厅，默认设备为烽火的设备
        $b_id = 46270;

        // 拿营业厅下的设备
        $devs = probe_dev_helper::get_devs($b_id);

        if ( !$devs ) {
            exit(-1);
        }

        $info = _model('setting')->read(array('field' => 'probe_record'));

        if ( $info ) {
            $offset = $info['value'];
        } else {
            $offset = 0;
        }

        $feng = device('fenghuo');

        // @todo    烽火pid
        $feng -> set_pid(10020);

        $max_id = 0;

        foreach ( $devs as $dev => $rssi ) {
            // 查询历史数据
            $id = $feng -> record($dev, $offset);

            if ( $id > $max_id ) {
                $max_id = $id;
            }

            /*
            foreach ( $data as $k => $v ) {
                if ( $v['id'] > $max_id ) {
                    $max_id = $v['id'];
                }

                // $mac = probe_helper::mac_decode($v['mac']);

                try {
                    $storage -> storage_mac(probe_helper::mac_decode($v['mac']), $v['rssi'], array(
                        'dev'   =>  $dev,
                        'time'  =>  $v['time']
                    ));
                } catch ( Exception $e ) {
                    trigger_error($e -> getMessage());
                }
            }
             */
        }

        if ( $info ) {
            _model('setting')->update(array('field' => 'probe_record'), array('value' => $max_id));
        } else {
            _model('setting')->create(array('field' => 'probe_record', 'value' => $max_id));
        }

        echo 'ok';
    }
    
    /**
     * 加载文件
     *
     * @param   String  文件名
     * @param   String  目录名
     *
     * @return  Void
     */
    public function check_mac_exist($params)
    {
        if (
               empty($params['b_id']) 
            || empty($params['mac'])
            || empty($params['start_date'])
            || empty($params['end_date'])
            ) {
            return 0;
        }

        $mac = probe_helper::mac_decode($params['mac']);

        $db   = get_db($params['b_id']);

        if (!$db) {
            return 0;
        }

        if (!_uri('probe_device' , ['business_id' => $params['b_id'] , 'status' => 1])) {
            return 1;
        }

        //判断探针设备是否正常
        if (!$db->read(['date' => date('Ymd')])) {
            return 0;
        }

        $sql  = " SELECT `dev`, `mac`, `remain_time`, `continued`, `is_indoor`, `is_oldcustomer`, `up_rssi`, `up_time` FROM `{$db -> table}` WHERE `mac` = '{$mac}'";

        if ($params['start_date'] == $params['end_date']) {
             $sql .= " AND `date` = {$params['start_date']} LIMIT 1";
        } else {
            $sql .= " AND `date` >= {$params['start_date']} AND `date` <= {$params['end_date']}  LIMIT 1";
        }

        // 查询今天的数据
        $info = $db -> getAll($sql);

        if ($info) {
            return 9;
        }

        return 2;
    }
}