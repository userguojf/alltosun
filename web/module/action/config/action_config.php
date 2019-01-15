<?php
/**
 * alltosun.com  Controller.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * Author: 孙先水(sunxs@alltosun.com)
 * Date:15/12/15
 *
 */
class action_config
{

    /**
     * 根据分组id获取res_name
     */
    public static $admin_res_name = array(
        '8'     => 'group',
        '23'    => 'province',
        '24'    => 'city',
        '25'    => 'area',
        '26'    => 'business_hall',

    );

    public static $admin_res_id = array(

        '8'     => 'supper',  //超极管理员
        '23'    => 'province_id',
        '24'    => 'cit',
        '25'    => 'dit',
        '26'    => 'business_hall',
    );

    public static $is_ajax_type = array(
        0 => '不显示',
        1 => '显示'
    );

    public static $is_auth_type = array(
            0 => '不限制',
            1 => '限制'
    );
    /**
     * 权限显示的icon
     */
    public static $icon_res_name =array(
        'content'     =>'iconfont-banner',
        'plate'     =>'iconfont-columns',
        'coupon'    =>'iconfont-youhui',
        'graphic'   =>'iconfont-tuwen',
        'count'     =>'iconfont-shujutongji',
        'advert'    =>'iconfont-guanggao',
        'user'      =>'iconfont-ren',
        'action'    =>'iconfont-quanxian',
        'log'       =>'iconfont-rizhi',
//         'spitslot'  =>'iconfont-tucao',
        'event'     => 'iconfont-youhui',
        'feedback'  => 'iconfont-feedback',
        'download' => 'iconfont-download',
        'web_configure' => 'iconfont-zhandian',
        'business_hall' => 'iconfont-columns',
        'faq'           => 'iconfont-feedback',
        'rfid'          => 'iconfont-rfid',
        'push'          => 'iconfont iconfont-dayinshebei',
        'screen'       => 'iconfont iconfont-columns',
        'probe_dev'    => 'iconfont-tanzhen',
        'screen_msg'   =>'iconfont-tucao',
    );

    public static $allow = array(
        101 => 'click_list/admin',
        102 => 'share/admin/pd',
        103 => 'share/admin/ibeacon',
        104 => 'share/admin/dm',
        105 => 'share/admin/awifi',
        106 => 'qydev/admin/service',
        107 => 'factory/admin',
    );
}