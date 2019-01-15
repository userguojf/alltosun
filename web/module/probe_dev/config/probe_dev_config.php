<?php
/**
 * alltosun.com  config.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-11-14 下午3:10:47 $
 * $Id$
 */

class probe_dev_config
{
    /**
     * 调用数字地图接口参数
     */
    public static $dm_param = array(
            'app_id'  => '9777880925419479',
            'app_key' => '3j4vwhA82tXnNVGrTIobKqGOJnkxJrCl',
    );

    /**
     * 数字地图各种地址
     * @var unknown
     */
    public static $dm_url = array(
            //开发环境根域名
            'develop_url' => 'http://market-mng-fe-dev.obaymax.com:20000/api',
            //测试环境根域名
            'test_url'    => 'http://market-mng-fe-test.obaymax.com:10000/api',
            //演示环境根域名
            'demo_url'    => 'http://market-mng-fe-temp.obaymax.com:30000/api',
            //线上环境
            'office_url'  => 'http://map.pzclub.cn/api',
    );
//     map.pzclub.cn
    /**
     * 调数字地图接口的操作探针的目的
     * @var unknown
     */
    public static $probe_operation = array(
            'create' => '/awifi/createProbeNo',
            'delete' => '/awifi/deleteProbeNo'
    );


    /**
     * 设备状态
     * @var unknown
     */
    public static $dev_status = array(
            1 => array(
                    'color'     => 'green',
                    'status'    => '设备在线'
            ),
            2 => array(
                    'color'     => 'rgb(230, 179, 61)',
                    'status'    => '设备离线'
            ),
            3 => array(
                    'color'     => 'yellow',
                    'status'    => '设备已申请'
            ),
            4 => array(
                    'color'     => 'yellow',
                    'status'    => '设备已发货'
            ),
            5 => array(
                    'color'     => 'yellow',
                    'status'    => '已收货待安装',
            ),
            6 => array(
                    'color'     => 'red',
                    'status'    => '已激活无数据',
            )
    );
}