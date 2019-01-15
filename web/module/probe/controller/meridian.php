<?php
/**
 * alltosun.com 子午线设备数据提交控制器 meridian.php
 * ============================================================================
 * 版权所有 (C) 2009-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王磊 (wangl@alltosun.com) $
 * $Date: 2017-5-9 下午12:05:44 $
*/

// load func.php
probe_helper::load('func');

class Action
{
    //wangjf 取消
    //use log;

    /**
     * 构造函数
     *
     * @return  Obj
     */
    public function __construct()
    {

    }

    /**
     * 设备提交数据接口
     *
     * @return  String
     */
    public function dataupload()
    {
        try {

            // 存储
            device('meridian')->storage();
        } catch (Exception $e) {
            probe_helper::write_log('meridian', $e -> getMessage());
        }
    }

    /**
     * 子午线设备登录接口
     *
     * @return  String
     */
    public function devicelogin()
    {
        // 登录到服务器
        device('meridian')->login();
    }

    /**
     * 设备与服务器握手接口
     *
     * @return  String
     */
    public function devicetrace()
    {
        // 握手
        device('meridian')->trace();
    }

    /**
     * 获取设备log
     *
     * @return  String
     */
    public function report_log()
    {
        // 设备上报自身log
        device('meridian')->report_log();
    }

    /**
     * 获取设备配置信息
     *
     * @return  String
     */
    public function get_config()
    {
        // 设备上报自身配置文件
        device('meridian')->report_config();
    }

    /**
     * 设备远程升级接口
     *
     * @return  String
     */
    public function checkupdate()
    {
        //probe_helper::write_log('checkupdate', '远程升级');
        // 检查更新
        device('meridian')->checkupdate();
    }

    /**
     * 设备升级版本
     *
     * @return  String
     */
    public function up_version()
    {
        // 下载版本文件
        device('meridian')->up_version();
    }

    /**
     * 设备升级配置文件
     *
     * @return  String
     */
    public function up_config()
    {
        // 下载配置文件
        device('meridian')->up_config();
    }

    public function test()
    {
        storage('day_storage')->write(array(
            array(
                'mac'   =>  'c4:0b:cb:42:12:63',
                'dev'   =>  '16120803',
                'rssi'  =>  -75,
                'time'  =>  time()
            )
        ));
    }
}
