<?php
/**
 * alltosun.com 焦点图 screen_config.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 申小宁 (shenxn@alltosun.com) $
 * $Date: 2016-1-5 下午3:21:18 $
 * $Id: screen_config.php 328618 2016-12-01 07:15:07Z guojf $
 */

class screen_config
{
    public static  $search_status =array(
        '0'=>'全部',
        '1'=>'未阅读',
        '2'=>'已阅读',
    );

    /**
     * 焦点图分类
     */
    public static $content_put_type = array(
            'group'         => '全国投放',
            'province'      => '全省投放',
            'city'          => '城市投放',
            'area'          => '区县投放',
            'business_hall' => '营业厅投放'
    );

    public static $search_type = array(
            0 => '全部',
            1 => '已上线',
            4 => '已下线',
            2 => '已过期',
            3 => '未开始',
    );

    public static $put_type = array(
            1 => array(
                0 => '仅保存',
                1 => '集团投放',
                2 => '部分投放'
            ),
            2 => array(
                0 => '仅保存',
                1 => '全省投放',
                2 => '部分投放'
            ),
            3 => array(
                0 => '仅保存',
                1 => '全市投放',
                2 => '部分投放'
            ),
            4 => array(
                0 => '仅保存',
                1 => '全区投放',
                2 => '部分投放'
            ),
            5 => array(
                0 => '仅保存',
                1 => '立即投放'
            )
    );

    public static $content_type = array(
            '0' => '全部',
            '1' => '图片',
            '2' => '视频',
            '3' => '链接',
            '4' => '机型宣传图'
    );

    /**
     * 调用数字地图接口创建和删除
     * added by guojf
     */
    public static $dm_type = array(
            1 => 'create',
            2 => 'delete',
    );

    /**
     * 调用数字地图接口参数
     */
    public static $dm_param = array(
            'app_id'  => '9777880925419479',
            'app_key' => '3j4vwhA82tXnNVGrTIobKqGOJnkxJrCl',
    );

    /**
     * 调用数字地图创建接口
     */
    //正式地址
    public static $dm_create_api_url      = "http://dm.pzclub.cn/api/awifi/createProbeNo";
    //测试地址
    public static $test_dm_create_api_url = 'http://market-mng-fe-dev.obaymax.com:20000/api/awifi/createProbeNo';

    /**
     * 调用数字地图删除接口
     */
    //正式地址
    public static $dm_delete_api_url      = "http://dm.pzclub.cn/api/awifi/deleteProbeNo";
    //测试地址
    public static $test_dm_delete_api_url = "http://market-mng-fe-dev.obaymax.com:20000/api/awifi/deleteProbeNo";
        
    /*
     * 饼状图的颜色
     */
    public static $pie_chart_color = array(
                0 => 'rgb(82, 131, 247)',
                1 => 'rgb(94, 201, 155)',
                2 => 'rgb(247, 198, 68)',
                3 => 'rgb(91, 98, 116)',
                4 => 'rgb(148, 210, 251)',
                5 => 'rgb(155, 212, 69)'
    );

    public static $category = array(
        '1'  => '活动',
        '2'  => '价签'

    );

    public static $screen_color_type = array(
        1 => array(0,0,0),//黑
        2 => array(255,255,255)//白
    );
}
?>