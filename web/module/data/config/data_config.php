<?php
/**
  * alltosun.com 数据配置文件 data_config.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年2月17日 下午4:50:32 $
  * $Id$
  */

class data_config
{
    /**
     * 数据更新类型
     */
    public static $update_record_type = array(
            '0' => '未知',
            '1' => '源数据(old_business)',
            '2' => '生产数据(business_hall)',
            '3' => '',
    );

    /**
     * 数据更新状态
     */

    public static $update_record_status = array(
            '0' => '更新中..',
            '1' => '完毕',
            '2' => '未完成'
    );

    /**
     * 缓存时间
     */
    public static $cache_time = 720000;

    /**
     * 缓存间隔，指定多长时间存入数据库一次
     */
    public static $cache_interval = 600;

    /**
     * 检测源数据中的区域本地是否存在
     */
    public static $exists_region = array(
            'province_region',
            'city_region',
            'area_region'
    );

    /**
     * member数据检测中每页检测的条数
     */
    public static $check_member_count = 1000;

    /**
     * 检测member 缓存最低储存时间
     */
    public static $check_member_cache_time  = 1800;


    /**
     * 省市区异常数据检测中每页检测条数
     */
    public static $exception_region_count = 100;

    /**
     * 省市区异常数据检测中缓存最低储存时间
     */
    public static $exception_region_cache_time  = 1800;

    /**
     * 异常错误号
     */
    public static $check_error = array(
            '1' => 'member不存在',
            '2' => 'member重复',
            '101' => '区县id为0或者本地不存在',
            '102' => '区县信息有误',
            '103' => '城市信息有误',
            '104' => '省份信息有误'
    );




}