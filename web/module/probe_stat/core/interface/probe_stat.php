<?php 
/**
 * alltosun.com  probe_stat.php
 * ============================================================================
 * 版权所有 (C) 2009-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王磊 (wangl@alltosun.com) $
 * $Date: 2017-5-31 下午12:33:37 $
*/

/**
 * 探针数据统计接口
 *
 * @author wangl
 */
interface probe_stat
{
    /**
     * 天统计
     *
     * @param   Array   参数
     */
    public function day_stat($param = array());

    /**
     * 24小时统计
     *
     * @param   Array   参数
    */
    public function hour_stat($param = array());

    /**
     * 天列表
     *
     * @param   Array   参数
    */
    public function day_list($param = array());

    /**
     * 小时列表
     *
     * @param   Array   参数
    */
    public function hour_list($param = array());

    /**
     * 设备品牌统计
     *
     * @param   Array   参数
    */
    public function brand_stat($param = array());

    /**
     * 新顾客统计
     *
     * @param   Array   参数
     */
    public function new_customer_stat($param = array());

    /**
     * 老顾客统计
     *
     * @param   Array   参数
     */
    public function old_customer_stat($param = array());
}
?>