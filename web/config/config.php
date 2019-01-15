<?php

/**
 * alltosun.com 网站配置文件 config.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author:申小宁 (shenxn@alltosun.com) $
 * $Date: 2017-12-28 下午02:38:03 $
 * $Id: config.php 383322 2017-11-26 10:12:05Z shenxn $
 */

/**
 * 定义是否在开发服务器上
 */
defined('ONDEV') || define('ONDEV', stripos($_SERVER['HTTP_HOST'], 'alltosun.net'));

/**
 * 定义HTTP协议头
 */
isset($_SERVER['SCHEME']) && $_SERVER['SCHEME'] == 'https' ? $SCHEME = 'https' : $SCHEME = 'http';

define('SCHEME', $SCHEME);

/**
 * 定义网站url
 */
define('SITE_URL', SCHEME . '://' . $_SERVER['HTTP_HOST']);



/**
 * 定义上传文件夹，没有目录分隔符
 */
define('UPLOAD_FOLDER', 'upload');

/**
 * 定义上传文件夹url
 */
define('UPLOAD_URL', SITE_URL . '/' . UPLOAD_FOLDER);

/**
 * 定义文件上传路径
 */
define('UPLOAD_PATH', ROOT_PATH . '/' . UPLOAD_FOLDER);

/**
 * 文件下载目录
 */
define('DOWNLOAD_PATH', UPLOAD_FOLDER . '/down');

/**
 * 静态文件url
 */
if (!ONDEV) {
    define('STATIC_URL', $SCHEME . '://m.pzclub.cn');
} else {
    define('STATIC_URL', SITE_URL);
}

/**
 * 静态文件目录
 */
define('STATIC_DIR', ROOT_PATH);

/**
 * AN SUPER POWER
 */
define('ANPOWER', ONDEV || (!ONDEV && Request::Get('powerby') == 'alltosun'));

/**
 * 定义是否开启debug调试信息的输出
 */
define('D_BUG', Request::Get("debug") && ANPOWER);

/**
 * 定义是否开启memcache的缓存（默认开启）
 * noted by guojf
 */
// define('CACHE', Request::Get("cache", 1));

/**
 * 定义是否开启memcache的缓存（默认开启）
 * 开发机无缓存
 * added by guojf
 */
if ( ANPOWER && Request::Get("cache", 1) == 0 || ONDEV ) {
    define('CACHE', 0);
} else {
    define('CACHE', Request::Get("cache", 1));
}

// define('CACHE', 0);

define('PROJECT_NS', '201711awifiprobe');                            // 定义项目自有的namespace（已用于MC）
define('SCRIPT_EMAIL', 'shenxn@alltosun.com');              // 定义执行计划任务等脚本发生错误时报告错误日志的邮箱
define('MERGE_BG', '#ffffff');                         // 缩略图补偿背景色


if (ONDEV) {
    //主库DB 营业厅相关模块读取  默认连接
    Config::set('db', array('mysqli', 'localhost', '201512awifi', 'REa4GHFUZUm86vCX', '201512awifi'));

    //探针分库相关
    Config::set('awifi_probe', array('mysqli', 'localhost', 'awifi_probe', '3YUerseFnReJqMpK', 'awifi_probe'));

    //探针相关数据库
    Config::set('awifi_probe_tz', array('mysqli', 'localhost', 'awifi_probe_tz', 'CZnZ7L622FuZrBjN', 'awifi_probe_tz'));

    //亮靓相关数据库
    Config::set('awifi_liangliang', array('mysqli', 'localhost', 'awifi_liangliang', '7DRmmeBTdJWqd6Qy', 'awifi_liangliang'));

    //rfid相关数据库
    Config::set('awifi_rfid', array('mysqli', 'localhost', 'awifi_rfid', '3YUerseFnReJqMpK', 'awifi_rfid'));

    //mongodb相关数据库
    Config::set('mongo', array('mongodb://192.168.2.21:27019', 'user', 'pass'));
    Config::set('mongo1', array('mongodb://192.168.2.21:27019', 'user', 'pass'));
    Config::set('mongo2', array('mongodb://192.168.2.21:27019', 'user', 'pass'));

    Config::set('mongo_conf', array(
        'screen' => 'mongo2'
    ));

    Config::set('module_db_conf',
        array(
            'action'              => 'db',
            'area'                => 'db',
            'area_bak'            => 'db',
            'business_hall'       => 'db',
            'city'                => 'db',
            'data_error_log'      => 'db',
            'data_update_record'  => 'db',
            'faq_record'          => 'db',
            'group'               => 'db',
            'group_action'        => 'db',
            'group_user'          => 'db',
            'member'              => 'db',
            'old_business'        => 'db',
            'probe_brand'         => 'db',
            'probe_business_rule' => 'db',
            'probe_device'        => 'db',
            'probe_log'           => 'db',
            'probe_rule'          => 'db',
            'probe_stat_day'      => 'db',
            'probe_stat_hour'     => 'db',
            'probe_update_log'    => 'db',
            'province'            => 'db',
            'public_contact_user' => 'db',
            'qydev_access_token'  => 'db',
            'rfid_api_logs'       => 'db',
            'rfid_error_logs'     => 'db',
            'rfid_shoppe'         => 'db',

            //门店改造申请表   songzy 20180507
            'apply'               => 'db',
            'convert_record'      => 'db',
            'apply_plan_res'      => 'db',
            'convert_apply'       => 'db',

            'api_log'                         => 'awifi_liangliang',
            'push_log'                        => 'awifi_liangliang',
            'screen_action_record'            => 'awifi_liangliang',
            'screen_api_log'                  => 'awifi_liangliang',
            'screen_business_stat_day'        => 'awifi_liangliang',
            'screen_business_stat_hour'       => 'awifi_liangliang',
            'screen_business_wifi_pwd'        => 'awifi_liangliang',
            'screen_click_record'             => 'awifi_liangliang',
            'screen_content'                  => 'awifi_liangliang',
            'screen_content_click_record'     => 'awifi_liangliang',
            'screen_content_click_stat_day'   => 'awifi_liangliang',
            'screen_content_res'              => 'awifi_liangliang',
            'screen_device'                   => 'awifi_liangliang',
            'screen_device_name_nickname'     => 'awifi_liangliang',
            'screen_device_online'            => 'awifi_liangliang',
            'screen_device_online_stat_day'   => 'awifi_liangliang',
            'screen_device_stat_day'          => 'awifi_liangliang',
            'screen_device_tag'               => 'awifi_liangliang',
            'screen_device_tag_res'           => 'awifi_liangliang',
            'screen_device_active_stat_month' => 'awifi_liangliang',    //wangjf add 月活跃统计
            'screen_page_record'              => 'awifi_liangliang',
            'screen_roll_business_stat'       => 'awifi_liangliang',
            'screen_roll_count_stat'          => 'awifi_liangliang',
            'screen_roll_device_stat'         => 'awifi_liangliang',
            'screen_show_pic'                 => 'awifi_liangliang',
            'screen_id_record'                => 'awifi_liangliang',
            'screen_continued_offline_record' => 'awifi_liangliang',
            'action'                          => 'awifi_liangliang',
            'screen_device_nickname'          => 'awifi_liangliang',
            'screen_device_price_stat'        => 'awifi_liangliang',
            'screen_device_price_record'      => 'awifi_liangliang',
            'screen_everyday_offline_record'  => 'awifi_liangliang',
            'screen_update_version_info'      => 'awifi_liangliang',
            'screen_offline_series_stat'      => 'awifi_liangliang',
            'screen_business_device_num_stat' => 'awifi_liangliang',
            'qydev_msg_log'                   => 'awifi_liangliang',
            'screen_qydev_msg_record'         => 'awifi_liangliang',
            'screen_roll_province_stat'       => 'awifi_liangliang',
            'screen_roll_city_stat'           => 'awifi_liangliang',
            'setting'                         => 'awifi_liangliang',
            'screen_show_pic_cache'           => 'awifi_liangliang',
            'screen_after_installing_offline_record' => 'awifi_liangliang',
            'message_log'                     => 'awifi_liangliang',
            'screen_redirect_url_cache'       => 'awifi_liangliang',
            'screen_auto_start'               => 'awifi_liangliang',
            'screen_content_set_meal'         => 'awifi_liangliang',

            'screen_content_meal'             => 'awifi_liangliang',
            'screen_content_meal_res'         => 'awifi_liangliang',
            'screen_content_meal_record'      => 'awifi_liangliang',
            'screen_content_meal_stat_day'    => 'awifi_liangliang',
            'screen_content_meal_pop_record'  => 'awifi_liangliang',
            'screen_content_meal_pop_stat_day' => 'awifi_liangliang',
            'message_log'                      => 'awifi_liangliang',
            'screen_redirect_url_cache'        => 'awifi_liangliang',
            //'rfid_label'                       => 'awifi_liangliang', 2018-01-27 wangjf注释， 测试环境的rfid_label在默认库中

            'screen_app_stat_day'             => 'awifi_liangliang',
            'screen_app_stat_week'            => 'awifi_liangliang',
            'screen_app_stat_month'           => 'awifi_liangliang',
            'screen_app_stat_year'            => 'awifi_liangliang',
            'screen_auto_start_business_stat' => 'awifi_liangliang',
            'screen_device_version_record'    => 'awifi_liangliang',
            'screen_auto_start_device_stat'   => 'awifi_liangliang',
            'gps_record'                      => 'awifi_liangliang',
//             'public_contact_user'             => 'awifi_liangliang',
            'qydev_news'                      => 'awifi_liangliang',
            'qydev_jsapi_ticket'              => 'awifi_liangliang',
            'comment'                         => 'awifi_liangliang',  //评论
            'like'                            => 'awifi_liangliang',  //点赞
            'screen_business_hall_coord'      => 'awifi_liangliang',  //wangjf add 营业厅处理后的坐标
            'qydev_news_operate_record'      => 'awifi_liangliang',
            'qydev_news_content'      => 'awifi_liangliang',
            'qydev_news_content_answer'      => 'awifi_liangliang',
            'qydev_news_content_zan_record'      => 'awifi_liangliang',
            'qydev_news_share'      => 'awifi_liangliang',
            'screen_file_data_record'        => 'awifi_liangliang', //wangjf add 内容统计的文件上传表
            'qydev_share_record'        => 'awifi_liangliang',
            // 企业微信 start
//             'wework_access_token' => 'awifi_liangliang',
//             'wework_api_log'      => 'awifi_liangliang',
//             'wework_department'   => 'awifi_liangliang',
//             'wework_user'         => 'awifi_liangliang',
//             'wework_test_record'  => 'awifi_liangliang',
            'screen_version_record' => 'awifi_liangliang',
            // 企业微信 end
            'files'               => 'awifi_liangliang', // 文件下载
            'file_record'         => 'awifi_liangliang', // 文件操作记录表

            'screen_business_hall_coord_diff' => 'awifi_liangliang', // 亮屏营业厅坐标对比 测试
            't_sys_store_data'                => 'awifi_liangliang', // 亮屏营业厅坐标对比 测试
            't_hall_info'                     => 'awifi_liangliang', // 亮屏营业厅坐标对比 测试

            'screen_device_offline_record' => 'awifi_liangliang',    //自动下架记录表
            'screen_tui_yyt_record' => 'awifi_liangliang',
            'screen_push_click_url_record' => 'awifi_liangliang',
            'screen_daily_hebave_record' => 'awifi_liangliang',
            'screen_content_customize_pic' => 'awifi_liangliang',
            // 低版本设备点击记录表
            'screen_old_device_click_record' => 'awifi_liangliang',
            'screen_old_device_business' => 'awifi_liangliang',
            'screen_daily_hebave_report_date_record' => 'awifi_liangliang',
            'screen_daily_behave_happening_record' => 'awifi_liangliang',
            'screen_daily_hebave_device_record' => 'awifi_liangliang',
            // 手机型号参数表
            'screen_device_params' => 'awifi_liangliang',
            // 手机型号参数test表
            'screen_device_params_test' => 'awifi_liangliang',
            'screen_content_make_pic_record' => 'awifi_liangliang',


            // 测试使用的表
            'use_test_record' => 'awifi_liangliang',
        )
    );

    //memcache
    Config::set('mc',
            array(
                    array('localhost', 11211),
                    array('localhost', 11212)
            )
    );

    Config::set(array(
        'cookie_domain'    => '201711awifiprobe.alltosun.net',
        'cookie_path'      => '/',
        'cookie_delimiter' => "\t",
    ));
} else {
    Config::set('db', array('mysql', 'DB01', '201512awifi', '9A4vDt3cFMq3Wmxc', '201512awifi'));
    Config::set('group_db', array('mysqli', 'DB01', '201512awifi', '9A4vDt3cFMq3Wmxc', '201512awifi'));
    Config::set('awifi_probe', array('mysql', 'DB03', 'awifi_probe', '3YUerseFnReJqMpK', 'awifi_probe'));
    Config::set('awifi_probe_01', array('mysqli', 'DB04', 'awifi_probe_tz', 'CZnZ7L6-22FuZrBjN', 'awifi_probe_01'));
//     Config::set('awifi_liangliang', array('mysql', 'MYCAT01:8066', 'awifi_liangliang', 'pSw4jMGUHPN9ZSv5', 'awifi_liangliang'));
    Config::set('awifi_liangliang', array('mysql', 'MYCAT01', 'root', 'e898f0fdef', 'awifi_liangliang'));

    Config::set('awifi_rfid', array('mysql', 'DB02', '201512awifi', '9A4vDt3cFMq3Wmxc!!@', '201512awifi'));

    Config::set('module_db_conf',
        array(
            'action'              => 'db',
            'area'                => 'db',
            'area_bak'            => 'db',
            'business_hall'       => 'db',
            'city'                => 'db',
            'data_error_log'      => 'db',
            'data_update_record'  => 'db',
            'faq_record'          => 'db',
            'group'               => 'db',
            'group_action'        => 'db',
            'group_user'          => 'db',
            'member'              => 'db',
            'old_business'        => 'db',
            'probe_brand'         => 'db',
            'probe_business_rule' => 'db',
            'probe_device'        => 'db',
            'probe_log'           => 'db',
            'probe_rule'          => 'db',
            'probe_stat_day'      => 'db',
            'probe_stat_hour'     => 'db',
            'probe_update_log'    => 'db',
            'province'            => 'db',
            'public_contact_user' => 'db',
            'qydev_access_token'  => 'db',
            'rfid_shoppe'     => 'db',
            'rfid_api_logs'   => 'db',
            'rfid_label'             => 'awifi_rfid',
            'rfid_online_stat_day'   => 'awifi_rfid',
            'rfid_phone'             => 'awifi_rfid',
            'rfid_probe_user_record' => 'awifi_rfid',
            'rfid_record'            => 'awifi_rfid',
            'rfid_record_detail'     => 'awifi_rfid',
            'rfid_rwtool'            => 'awifi_rfid',
            'rfid_rwtool_stat_day'   => 'awifi_rfid',
            'rfid_stat'              => 'awifi_rfid',
            'rfid_stat_hour'         => 'awifi_rfid',
            'rfid_error_logs'        => 'awifi_rfid',

            'api_log'                         => 'awifi_liangliang',
            'push_log'                        => 'awifi_liangliang',
            'screen_api_log'                  => 'awifi_liangliang',
            'screen_business_stat_day'        => 'awifi_liangliang',
            'screen_business_wifi_pwd'        => 'awifi_liangliang',
            'screen_content'                  => 'awifi_liangliang',
            'screen_content_res'              => 'awifi_liangliang',
            'screen_continued_offline_record' => 'awifi_liangliang',
            'screen_device'                   => 'awifi_liangliang',
            'screen_device_name_nickname'     => 'awifi_liangliang',
            'screen_device_online_stat_day'   => 'awifi_liangliang',
            'screen_device_tag'               => 'awifi_liangliang',
            'screen_device_tag_res'           => 'awifi_liangliang',
            'screen_device_version_nickname'  => 'awifi_liangliang',
            'screen_device_active_stat_month' => 'awifi_liangliang',    //wangjf add 月活跃统计
            'screen_error_log'                => 'awifi_liangliang',
            'screen_id_record'                => 'awifi_liangliang',
            'screen_install_qydev_record'     => 'awifi_liangliang',
            'screen_install_qydev_stat'       => 'awifi_liangliang',
            'screen_page_record'              => 'awifi_liangliang',
            'screen_roll_business_stat'       => 'awifi_liangliang',
            'screen_roll_count_stat'          => 'awifi_liangliang',
            'screen_roll_device_stat'         => 'awifi_liangliang',
            'screen_show_pic'                 => 'awifi_liangliang',
            'screen_show_pic_cache'           => 'awifi_liangliang',
            'screen_sign'                     => 'awifi_liangliang',
            'screen_sign_res'                 => 'awifi_liangliang',
            'screen_spitslot'                 => 'awifi_liangliang',
            'screen_version'                  => 'awifi_liangliang',
            'screen_everyday_offline_record'  => 'awifi_liangliang',
            'screen_device_nickname'          => 'awifi_liangliang',
            'screen_update_version_info'      => 'awifi_liangliang',
            'screen_offline_series_stat'      => 'awifi_liangliang',
            'screen_business_device_num_stat' => 'awifi_liangliang',
            'screen_qydev_msg_record'         => 'awifi_liangliang',
            'screen_roll_province_stat'       => 'awifi_liangliang',
            'screen_roll_city_stat'           => 'awifi_liangliang',
            'screen_after_installing_offline_record' => 'awifi_liangliang',
            'message_log'                     => 'awifi_liangliang',
            'screen_redirect_url_cache'       => 'awifi_liangliang',
            'screen_auto_start'               => 'awifi_liangliang',
            'screen_content_set_meal'         => 'awifi_liangliang',
            'screen_device_price_record'      => 'awifi_liangliang',
            'screen_version_record'           => 'awifi_liangliang',

            'screen_content_meal'             => 'awifi_liangliang',
            'screen_content_meal_res'         => 'awifi_liangliang',
            'screen_content_meal_record'      => 'awifi_liangliang',
            'screen_content_meal_stat_day'    => 'awifi_liangliang',
            'screen_content_meal_pop_record'  => 'awifi_liangliang',
            'screen_content_meal_pop_stat_day' => 'awifi_liangliang',
            'message_log'                      => 'awifi_liangliang',
            'screen_redirect_url_cache'        => 'awifi_liangliang',

            'screen_app_stat_day'             => 'awifi_liangliang',
            'screen_app_stat_week'            => 'awifi_liangliang',
            'screen_app_stat_month'           => 'awifi_liangliang',
            'screen_app_stat_year'            => 'awifi_liangliang',
            'screen_auto_start_business_stat' => 'awifi_liangliang',
            'screen_device_version_record'    => 'awifi_liangliang',
            'screen_auto_start_device_stat'    => 'awifi_liangliang',
            'gps_record'                      => 'awifi_liangliang',
            'comment'                         => 'awifi_liangliang',  //评论
            'like'                            => 'awifi_liangliang',  //点赞
            'screen_business_hall_coord'      => 'awifi_liangliang',  //wangjf add 营业厅处理后的坐标
            // 企业微信 start
            'screen_file_data_record'         => 'awifi_liangliang',
            'screen_device_offline_record' => 'awifi_liangliang',    //自动下架记录表
            'screen_tui_yyt_record' => 'awifi_liangliang',
            'screen_push_click_url_record' => 'awifi_liangliang',
            'screen_daily_hebave_record' => 'awifi_liangliang',
            'screen_daily_hebave_report_date_record' => 'awifi_liangliang',
            'screen_daily_behave_happening_record' => 'awifi_liangliang',
            'screen_daily_hebave_device_record' => 'awifi_liangliang',
            'screen_content_make_pic_record' => 'awifi_liangliang', 
            'screen_content_customize_pic' => 'awifi_liangliang',

            'screen_content_make_pic_record' => 'awifi_liangliang'

        )
    );

    //mongodb相关数据库
    Config::set('mongo', array('mongodb://MONGOS01:20000,MONGOS02:20000,MONGOS03:20000', 'user', 'pass'));

    Config::set('mongo_conf', array(
        'screen' => 'mongo'
    ));

    Config::set('mc', array(
        array('MC01', 11211),
        array('MC02', 11211)
    ));

    Config::set(array(
        'cookie_domain'    => 'pzclub.cn',
        'cookie_path'      => '/',
        'cookie_delimiter' => "\t",
    ));
}

Config::set(array(
    'template_dir' => ROOT_PATH . '/template/default',
    'compile_dir'  => DATA_PATH . '/smarty_compile'
));

// Controller、Memcache配置
Config::set(array(
    'head_meta_charset' => 'utf-8',
    // Controller Dir
    'controller_dir'    => ROOT_PATH . '/controller',
    // 设置上传图片的最大尺寸
    'image_max_size'    => 8097152,
    // 允许上传的附件类型
    'allow_type'        => array('jpg', 'png', 'gif', 'jpeg', 'bmp', 'flv', 'swf', 'rar', 'zip', 'msoffice', 'xls', 'xlsx', 'doc', 'docx', 'txt', 'psd', 'mp3', 'mp4', 'mpeg-4', 'mpeg4'),
    // 允许上传的图片类型
    'allow_image_type'  => array('jpg', 'png', 'gif', 'jpeg', 'bmp', 'xls'),
    // 允许上传的flash类型
    'allow_flash_type'  => array('flv', 'swf')
));

// 定义网站采用的jQuery版本
define('JQUERY_VER', '');
// 定义网站采用的jQuery-ui版本
define('JQUERY_UI_VER', '1.10.4');
// 定义网站是否采用google cdn的jquery和jquery-ui
define('JQUERY_GOOGLE_CDN', false);

/**
 * SMARTY目录
 */
define('SMARTY_TEMPLATE_DIR', Config::get('template_dir'));

// 是否开启url rewrite
Config::set('rewrite_on', true);
// 是否开启伪静态化
Config::set('html_static', false);

// 验证码的配置 根据项目看是否需要
Config::set('captcha', array(
    // 图片宽度
    'image_width'      => 275,
    // 图片高度
    'image_height'     => 90,
    // 验证码个数
    'code_length'      => 4,
    // 图片扭曲度
    'perturbation'     => 0.5,
    // 图片背景色
    'image_bg_color'   => '#F7FEEC',
    // 文字颜色
    'text_color'       => '#81BA4E',
    // 线条数
    'num_lines'        => 2,
    // 线条颜色
    'line_color'       => '#81BA4E',
    // 是否使用多彩文字
    'use_multi_text'   => false,
    // 多彩文字的随机色
    'multi_text_color' => array('#FF0000', '#FFFF00', '#00FF00'),
    // 随机的背景（绝对路径）
    'background_image' => array()
));

// 定义资源缩略图 array('res_type' => array('mode'=>'cut/max/merge', 'size'=>array(宽, 高)));
// 根据项目看是否需要
Config::set('res_thumb', array(
        //焦点图
        'focus'          => array(
            'big' => array(750, 400)
        ),
        'coupon'         => array(  // 优惠活动 活动宣传图
            'big'   => array(750, 400),
            'small' => array(250, 133)
        ),
        'coupon_ad'      => array(  // 优惠活动 app下载通栏
            'big' => array(750, 190)
        ),
        'coupon_index'   => array(  //首页 优惠图片显示
            'big' => array(320, 240)
        ),
        'coupon_content' => array(
            'mode' => 'max',
            'big'  => array(670, 0)
        ),
        'coupon_pics'    => array(
            'big'   => array(750, 400),
            'small' => array(250, 133)
        ),
        'graphic'        => array(  //图文
            'big'   => array(750, 400),
            'small' => array(250, 133)
        ),
        'graphic_cover'  => array(  //图文封面
            'big' => array(320, 240),
        ),
        'advert'         => array(  // 广告图片
            'big'   => array(260, 320),
            'small' => array(100, 100)
        ),
        'bgmap'          => array( // 背景图
            'big' => array(750, 1184)
        ),
        'transitional'   => array( // 过渡页
            'big' => array(640, 269)
        ),
        'comment'        => array(
            'mode'   => 'cut',
            'middle' => array(85, 85)
        ),
        'plate'          => array(
            'small' => array(50, 50)
        ),
        'video'          => array(
            'small' => array(320, 240)
        )
    )
);

//天翼平台app标示
Config::set('app_id', 137745160000035984);
Config::set('app_secret', '851924ddd645dedb265c89d9dac103c3');

//电信智慧门店 短信模板
Config::set('open189_aid', '369537000000265640');
Config::set('open189_akey', '288f2cf144fe4f7439b468feb035478a');

//授权地址
Config::set('authorize_api', 'https://oauth.api.189.cn/emp/oauth2/v3/authorize');
//授权回调地址
Config::set('redirect_uri', 'http://cps.live.189.cn/index.php?url=user/admin_login/callback');
//获取token
Config::set('token_api', 'https://oauth.api.189.cn/emp/oauth2/v3/access_token');
//调用方式
Config::set('response_type', 'token');

if (ONDEV) {
    // openapi配置
    Config::set('openapi', array(
            // appkey, skey, callback, 储存connect的位置
            'weixin' => array('wx34fe140aceb31d37', '41a80fb6fb928d6deead15ed52082f9a', SITE_URL . '/openapi/weixin/callback', 'db'), // 客户的
        )
    );
} else {
    // openapi配置
    Config::set('openapi', array(
//             appkey, skey, callback, 储存connect的位置
//     'weixin'       => array('wxfff4cb426ax651537', 'd1d90f95ede6edf4e31c75d874c86cac', SITE_URL.'/openapi/weixin/callback', 'db'), // 客户的
        )
    );
}


//正式平台暂时不上线
include_once __DIR__ . '/config_log.php';