<?php

// load func.php
probe_helper::load('func');

/**
 * table trait
 *
 * @author wangl
 */
trait table {
    /**
     * 创建数据表
     *
     * @param   Int 营业厅ID
     *
     * @return  Bool
     */
    public function create_table($b_id)
    {
        if ( !$b_id ) {
            return false;
        }

        // 操作数据库对象
        $db  = get_db($b_id, 'hour', true);
        // SQL
        $sql = "CREATE TABLE IF NOT EXISTS `{$db -> table}` (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `dev` varchar(50) NOT NULL DEFAULT '' COMMENT '设备',
            `b_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '营业厅ID',
            `date` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '时间',
            `mac` varchar(30) NOT NULL DEFAULT '' COMMENT 'mac地址',
            `frist_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '首次探测时间',
            `up_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后探测时间',
            `frist_rssi` int(11) NOT NULL DEFAULT '0' COMMENT '首次探测信号值',
            `up_rssi` int(11) NOT NULL DEFAULT '0' COMMENT '最后信号',
            `indoor_num` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '连续探测到室内次数',
            `remain_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '停留时长',
            `continued` int(10) UNSIGNED NOT NULL DEFAULT '0',
            `is_indoor` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否是室内',
            `is_oldcustomer` TINYINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否为老顾客',
            `time_line` text NOT NULL COMMENT '时间线',
            `add_time` datetime NOT NULL,
            `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `date` (`date`),
            KEY `mac` (`mac`),
            KEY `b_id` (`b_id`),
            KEY `date_mac_b_id_frist_time` (`date`,`mac`,`b_id`,`frist_time`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='探针按小时记录';";

        // 创建表
        $db -> getAll($sql);

        // 操作数据库对象
        $db = get_db($b_id, 'day', true);
        // SQL
        $sql = "CREATE TABLE IF NOT EXISTS `{$db -> table}` (
            `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            `dev` varchar(50) NOT NULL DEFAULT '' COMMENT '探测设备',
            `b_id` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '营业厅ID',
            `date` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '探测时间',
            `mac` varchar(30) NOT NULL DEFAULT '' COMMENT 'mac地址',
            `frist_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '首次探测时间',
            `up_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '最后探测时间',
            `frist_rssi` int(11) NOT NULL DEFAULT '0' COMMENT '首次探测信号',
            `up_rssi` int(11) NOT NULL DEFAULT '0' COMMENT '最后探测信号',
            `remain_time` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '停留时长',
            `continued` int(10) UNSIGNED NOT NULL DEFAULT '0',
            `is_indoor` int(10) UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否为室内人数',
            `is_oldcustomer` TINYINT UNSIGNED NOT NULL DEFAULT '0' COMMENT '是否为老顾客',
            `add_time` datetime NOT NULL,
            `update_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            KEY `date` (`date`),
            KEY `mac` (`mac`),
            KEY `b_id` (`b_id`),
            KEY `date_mac_dev_b_id` (`date`,`mac`,`dev`,`b_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='探针按天记录';";

        // 创建数据表
        $db -> getAll($sql);

        return true;
    }

    /**
     * 删除数据表
     *
     * @param   Int 营业厅ID
     *
     * @return  Bool
     */
    public function delete_table($b_id)
    {
        if ( !$b_id ) {
            return false;
        }

        // 按天记录表
        $db = get_db($b_id);

        // 删除表
        $db -> getAll("DROP TABLE IF EXISTS `{$db -> table}`;");

        // 按小时记录表
        $db = get_db($b_id, 'hour');

        $db -> getAll("DROP TABLE IF EXISTS `{$db -> table}`;");

        return true;
    }

    /**
     * 在所有营业厅表中执行sql
     *
     * @param   String  SQL
     * @param   String  类型
     *
     * @return  Bool
     */
    public function exec_sql($sql, $type = 'day')
    {
        if ( !$sql ) {
            return false;
        }

        // 获取有设备的营业厅列表
        $list = probe_dev_helper::get_list(array('status'=>1), 'GROUP BY `business_id` ');

        // 遍历列表
        foreach ($list as $k => $v) {
            // 获取操作数据对象
            $db = get_db($v['business_id'], $type);
            // 执行SQL
            $db -> getAll(sprintf($sql, $db -> table));
        }

        return true;
    }
}