<?php
/**
 * alltosun.com  offline.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-12-18 上午11:44:10 $
 * $Id$
 */
class Action
{
    public function index()
    {
        _widget('screen_stat.offline_stat')->roll_poling_device();
    }
 
    public function delete()
    {
//         _model('screen_business_device_num_stat')->delete(array(1 => 1));
//         _model('screen_offline_series_stat')->delete(array(1 => 1));
//         _model('screen_id_record')->delete(array('data_table' => 'screen_device','date' => 20171218));
    }
}