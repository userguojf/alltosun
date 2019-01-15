<?php
/**
 * alltosun.com  rfid_config.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-5-10 上午11:01:30 $
 * $Id$
 */

class rfid_config
{
    /**
     * 手机具体信息添加方式
     */
    public static $add_type = array(
            1 => '单独添加',
            2 => '批量添加',
    );

    /**
     * 调用数字地图接口参数
     */
    public static $dm_param = array(
            'app_id'  => '9777880925419479',
            'app_key' => '3j4vwhA82tXnNVGrTIobKqGOJnkxJrCl',
    );

    /**
     * 调用数字地图接口创建和删除
     */
    public static $dm_type = array(
            1 => 'create',
            2 => 'delete',
    );

    /**
     * 调用数字地图创建接口
     */
    //正式地址
    public static $dm_create_api_url     = "http://dm.pzclub.cn/api/awifi/createProbeNo";
    //测试地址
    public static $test_dm_create_api_url ='http://market-mng-fe-dev.obaymax.com:20000/api/awifi/createProbeNo';

    /**
     * 调用数字地图删除接口
     */
    //正式地址
    public static $dm_delete_api_url      = "http://dm.pzclub.cn/api/awifi/deleteProbeNo";
    //测试地址
    public static $test_dm_delete_api_url = "http://market-mng-fe-dev.obaymax.com:20000/api/awifi/deleteProbeNo";

     /**
     * 调用数字地图创建接口
     */
//     public static $dm_create_api_url = 'http://market-mng-test.obaymax.com/awifi/createProbeNo';
//     public static $dm_create_api_url ='http://market-mng-temp.obaymax.com/awifi/createProbeNo';
//     public static $dm_create_api_url = 'http://market-mng-test.obaymax.com/awifi/createProbeNo';
//     public static $dm_create_api_url = 'http://dm.pzclub.cn/api/awifi/createProbeNo';;

    /**
     * 调用数字地图删除接口
     */
//     public static $dm_delete_api_url ="http://dm.pzclub.cn/api/awifi/deleteProbeNo";
//     public static $dm_delete_api_url = "http://market-mng-temp.obaymax.com/awifi/deleteProbeNo";


    /**
     * 手机添加信息 选择颜色如下
     */
    public static $phone_colors = array(
            1 => '红色',
            2 => '橙色',
            3 => '黄色',
            4 => '绿色',
            5 => '蓝色',
            6 => '靛色',
            7 => '紫色',
            8 => '灰色',
            9 => '粉色',
            10 => '黑色',
            11 => '白色',
            12 => '棕色'
    );

    /**
     * 统计日期所支持的类型
     * @var unknown
     */
    public static $stat_date_type = array(
            'hour',
            'day',
            'week',
            'month',
    );

    /**
     * 接收的数据字段
     * @var unknown
     */
    public static $receive_fields = array(
            'shanj' => array(
                    'mac',     //设备物理地址
                    'alias',   //设备别名
                    'start',   //拿起时间
                    'end',     //放下时间
                    'duration',//拿起放下的时长
                    'xyz_a',   //三轴加速度数据
                    'xyz_g',   //三轴陀螺仪数据
                    'rssi',    //信号强度
                    'q',       //电量
                    'h_packet' //心跳包
            ),
    );

    /**
     * 设备状态
     * @var unknown
     */
    public static $rwtool_status = array(
            1 => array(
                    'color'     => 'green',
                    'status'    => '读写器正常(标签正常)'
            ),
            2 => array(
                    'color'     => 'rgb(230, 179, 61)',
                    'status'    => '读写器正常(部分标签离线)'
            ),
            6 => array(
                    'color'     => 'red',
                    'status'    => '读写器异常(全部标签离线)',
            ),
//             3 => array(
//                     'color'     => 'yellow',
//                     'status'    => '设备已申请'
//             ),
//             4 => array(
//                     'color'     => 'yellow',
//                     'status'    => '设备已发货'
//             ),
//             5 => array(
//                     'color'     => 'yellow',
//                     'status'    => '已收货待安装',
//             )
    );
}