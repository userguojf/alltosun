<?php
/**
 * alltosun.com  business_hall_config.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2016-7-26 下午4:13:15 $
 * $Id$
 */
class business_hall_config
{
   public static $type = array(
           1 => '集团',
           2 => '省级',
           3 => '市级',
           4 => '地区级',
           5 => '营业厅级'
   );

   public static $activity = array(
           0 => '不活跃',
           1 => '活跃'
   );


   public static $count_page = 2;

   public static $search_type = array(
           'undefined'      => '搜索类型',
           'user_number'    => '渠道编码',
           'contact'        => '联系人',
   );

   public static $hall_type = array(
           '0'              => '未分类',
           '1'              => '社区店',
           '2'              => '商圈店',
           '3'              => '旗舰店',
           '4'              => '其他'
   );

   public static $hall_level = array(
           '0'              => '未划分',
           '1'              => '1级',
           '2'              => '2级',
           '3'              => '3级',
           '4'              => '4级',
           '5'              => '5级',   );

   public static $search_store_type = array(
           'undefined'      => '所有自有厅',
           '1'              => '1级',
           '2'              => '2级',
           '3'              => '3级',
           '4'              => '4级',
           '5'              => '5级',
           '101'              => '社区店',
           '102'              => '商圈店',
           '103'              => '旗舰店',
           '104'              => '其他'   );

   public static $search_connect_type = array(
           'undefined'      => '所有接入类型',
           'chinanet'       => 'Chinanet接入',
           'fatAp'          => '胖AP接入',
           'optical modem'  => '定制光猫接入',
           'others'         => '其他'
   );

   public static $res_name_type = array(
           'group' => '全国级',
           'province' => '省级',
           'city'   => '市级',
           'area'   => '县级',
           'business_hall' => '厅级',
   );

}
