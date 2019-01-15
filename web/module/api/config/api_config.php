<?php

/**
 * alltosun.com 用户配置 user_config.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: Shenxn 申小宁 (shenxn@alltosun.com) $
 * $Date: Sep 16, 2013 2:10:36 PM $
 * $Id$
 */

class api_config
{
    public static $_yhd_order    = 'http://unioncps.yhd.com/common/service.do';
    public static $_wbiao_order  = 'http://cps.wbiao.cn/api/';
    public static $_newegg_order = '';
    public static $_wine9_order  = '';

    public static $res_names = array('hot_goods','discount_goods','group_goods','goods_wine');

    public static $sources = array(
            'bagtree',
            'yhd',
            'vip',
            'manzuo',
            'jiuxian'
    );

    public static $login_url = 'http://toe.51awifi.com/eapi/app/paramdecrypt';

    public static $default_url = 'http://201512awifi.alltosun.net/upload/2016/04/10/20160410105749000000_1_69601_9.jpg';

    public static $appid_list =  array(
        'wifi_js_kyql40ekax9bxvqu',
        'wifi_fj_kyql40ekax9bxbso',
        'wifi_awifi_zvaz7hiiaib7yuwt',
        'wifi_sh_xi0Gz7hb7yuwtTo9',
        'wifi_jswwp_fmakxk3qqdqg6edp',
        //排队平台
        'wifi_paidui_e084485fu23h7hf8' => 'b9aca85c819a8d59b1277d71da49b068',
    );

    /**
     * appid列表 wangjf
     */
    public static $appid_list_by_login = array(

            //数字地图
            'wifi_shujdt_awzdxhyadrtggbrd' => 'd1cb99814ddc2d11cdd8c099b6e5c6e8',

            //江苏卖乐多商城
            'wifi_jsmld_k39x0wjt38siow0w' => 'b4d80c7b4c3aeb5b5636bbaf6f2aef97',
            //微旺铺
            'wifi_jswwp_fmakxk3qqdqg6edp' => 'bj64ibhxxdzdwkjknxqhpzpomhuqhdvo',

            //请求Awifi平台所需
            'wifi_mac_awzdxhyakax9b1rw'    => 'qra4s9w2badfde0f3665dsf28322b4b2',

            //电信Awifi请求本平台所需, 目前用于专柜同步
            'wifi_dxawifi_j29sod9dawfe29d2' => '83136817debff9b6ab2e5b0269695137',

            //排队平台
            'wifi_paidui_e084485fu23h7hf8' => 'b9aca85c819a8d59b1277d71da49b068',

            //交互屏
            'wifi_jhpscreen_bw2pvw40u23fv92e' => '3e25e6807b1078b59e5713250f42ccce'    );

    /**
     * 营业厅数据接口    wangjf
     * @var array
     */
    public static $appid_list_by_hall_data = array(
            //数据地图
            //key 是appid val 是appKey
            'wifi_shujdt_awzdxhyadrtggbrd' => 'd1cb99814ddc2d11cdd8c099b6e5c6e8',
            //江苏
            'wifi_js_kyql40ekax9bxvqu'     => 'ef6aea422962bade0f3665882b4bb395'
    );

    // 接口请求来源(端)
    public static $source = array(
        '1001' => 'ios',
        '1002' => 'android',
        '1003' => 'h5',
        '1004' => 'pc',
        '1010' => 'test'
    );

    // 接口请求通用key
    public static $source_key = 'alltosun2016';

    public static $white_list = array(
        'http://market-mng-fe-test.obaymax.com:10000',
        'http://market-mng-fe-dev.obaymax.com:20000',
        'http://market-mng-fe-temp.obaymax.com:30000',
        'http://market-mng-console-fe-dev.obaymax.com',
        'http://market-mng-console-fe-test.obaymax.com',
        'http://market-mng-console-fe-temp.obaymax.com',
        'http://dm.pzclub.cn',
        'http://map.pzclub.cn'
    );
    /**
     * 接口返回值的定义
     * @var unknown
     */
    public static $error_code = array(
            '20001' => '营业厅渠道码不能为空',
            '20002' => '未找到对应渠道码的账号信息',
    );
}
?>
