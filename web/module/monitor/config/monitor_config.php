<?php
/**
  * alltosun.com 监视模块配置文件 monitor_config.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2016-7-7 上午11:31:52 $
  * $Id$
  */
class monitor_config
{
    /**
     * 监控为空数据的mysql数据表
     */
    public static $monitor_empty_data_table = array(
            //rfid 记录表 监控配置
            'rfid_record_detail'            => array(
                    'title'                 => 'RFID记录表', //表昵称
                    'table_type'            => 'mysql',     //表类型，数据库类型
                    'waraing_time_threshold' => 3600, //报警阀值，指定多长时间无数据启动报警
                    'waraing_time_interval'  => 3600,       //报警时间间隔
                    'update_time_field'     => 'update_time', //表示更新时间的字段， 用来比较和获取最后一次数据操作时间
                    'order_field'           => 'id', //排序字段
                    'start_time'            => '08:30:00',  //监控时间段
                    'end_time'              => '18:30:00',  //监控时间段
                    'waraing_email'         => array(       //报警邮箱
                            'wangjf@alltosun.com',
                            'shenxn@alltosun.com',
                            'guojf@alltosun.com',
                    ),
            ),

            //亮屏在线表 监控配置
            'screen_device_online'   => array(
                    'title'                 => '亮靓在线记录表',     //表昵称
                    'table_type'            => 'mongodb',            //表类型，数据库类型
                    'waraing_time_threshold' => 3600,                 //报警阀值，指定多长时间无数据启动报警
                    'waraing_time_interval'  => 3600,       //报警间隔时间
                    'update_time_field'     => 'add_time', //表示更新时间的字段， 用来比较和获取最后一次数据操作时间
                    'order_field'           => '_id', //排序字段
                    'start_time'            => '08:30:00',  //监控时间段
                    'end_time'              => '18:30:00',  //监控时间段
                    'waraing_email'         => array(       //报警邮箱
                            'wangjf@alltosun.com',
                            'guojf@alltosun.com',
                            'shenxn@alltosun.com',
                    ),
            ),

            //探针监控配置
            'probe_1_17_120_46120_day' => array(
                    'title'                 => '探针西单营业厅天统计表',     //表昵称
                    'table_type'            => 'mysql',            //表类型，数据库类型
                    'waraing_time_threshold' => 3600,                 //报警阀值，指定多长时间无数据启动报警
                    'waraing_time_interval'  => 3600,       //报警间隔时间
                    'update_time_field'     => 'update_time', //表示更新时间的字段， 用来比较和获取最后一次数据操作时间
                    'order_field'           => 'update_time', //排序字段
                    'start_time'            => '08:30:00',  //监控时间段
                    'end_time'              => '18:30:00',  //监控时间段
                    'waraing_email'         => array(       //报警邮箱
                            'wangjf@alltosun.com',
                            'shenxn@alltosun.com',
                            'guojf@alltosun.com',
                    ),
            ),

    );

    /**
     * 手机号
     * @var unknown
     */
    public static $phone_array = array(
            '15001114056'
    );

}