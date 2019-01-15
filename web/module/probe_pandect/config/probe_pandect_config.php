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

class probe_pandect_config
{   
    
    
    
    public static $search_type = array(
            0 => '探针',
            1 => 'rfid'
    );
    
    public static $searchs_type = array(
            0 => '设备类型',
            1 => '探针',
            2 => 'rfid'
    );
    
    public static $order_type = array(
            0 => '待发货',
            1 => '已发货',
            2 => '已拒绝'
    );
    
    public static $search_flag = array(
            0 => '正确数据一览',
            1 => '错误数据一览'
    );
    
    
    public static $business_level = array(
           '0' => '未划分',
           '1' => '一级厅',
           '2' => '二级厅',
           '3' => '三级厅',
           '4' => '四级厅',
           '5' => '五级厅'
    );
    
    
    /**
     * 设备状态
     * @var unknown
     */
    public static $probe_status = array(
            '1' => '待审批',
            '2' => '已取消'
    );
    /**
     * 调数字地图接口的操作探针的目的
     * @var unknown
     */
    public static $probe_operation = array(
            'create' => '/awifi/createProbeNo',
            'delete' => '/awifi/deleteProbeNo'
    );
    
    
   /**
    * 集团设备状态
    * @var unknown
    */
    public static $dev_status = array(
            1 => array(
                    'color'     => 'green',
                    'status'    => '设备正常'
            ),
            2 => array(
                    'color'     => 'rgb(230, 179, 61)',
                    'status'    => '设备离线'
            ),
            6 => array(
                    'color'     => 'red',
                    'status'    => '已激活无数据',
            )
    );
    
    
    public static $search_status = array(
            '0' => '申请状态',
            '1' => '待审核',
            '2' => '已取消'
    );
    
   
}