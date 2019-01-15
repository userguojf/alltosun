<?php
/**
  * alltosun.com 专柜配置 shoppe_config.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年8月23日 下午5:01:21 $
  * $Id$
  */
class shoppe_config
{
    /**
     * 调用数字地图接口参数
     * 数字地图接口文档 http://market-mng-dev.obaymax.com/swagger-ui.html
     */
    public static $dm_api_config = array(
            'appid'  => '9777880925419479',
            'appkey' => '3j4vwhA82tXnNVGrTIobKqGOJnkxJrCl',
    );

    public static $from = array(
            '0' => '未知',
            '1' => 'RFID',
            '2' => '数字地图',
            '3' => '亮屏',
            '4' => '专柜管理',
    );
}