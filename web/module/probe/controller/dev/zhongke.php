<?php 
/**
 * alltosun.com  zhongke.php
 * ============================================================================
 * 版权所有 (C) 2009-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王磊 (wangl@alltosun.com) $
 * $Date: 2017-6-7 上午11:02:28 $
*/

// load func.php
probe_helper::load('func');

class Action
{
    /**
     * 设备提交数据接口
     *
     * @return  String
     */
    public function index()
    {
        device('zhongke')->storage();
    }
}
