<?php
/**
 * alltosun.com 推送配置文件 push_config.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 雷健雄 (leijx@alltosun.com) $
 * $Date: 2015-4-3 下午4:37:10 $
 * $Id: push_config.php 55330 2017-01-23 07:47:39Z wangdk $
 */

class push_config
{
    /**
     * 推送配置
     * @var unknown
     */
    public static $conf = array(
            'interface'     => 'https://api.jpush.cn/v3/push',

            // 推送APPKey
            'manage_appkey'               => '53808187860f92c2aa0d789d',
            'manage_master_secret'        => '33fba8e67a9f65a8e0e9417e',
    );


    /**
     * 推送初始化信息
     * @var unknown
     */
    public static $init_infos = array(
            // 平台
            'platform' => 'ios,android',
            // 推送设备对象
            'audience' => 'all',
            // 要发送的消息体
            'notification' => array(
                        'alert'   => '',
                        'android' => array(
                                'builder_id' => 1,
                                // 'extras'     => array()
                        ),
                        'ios'     => array(
                                'sound'      => 'default',
                                'badge'      => '1',
                                // 'extras'     => array()
                        ),
            ),
            'options'  => array(
                    // 离线消息保存时间
                    'time_to_live'    => 60,
                    // 指定推送环境，false表示开发环境
                    'apns_production' => false
            )
    );


    /**
     * http返回的状态码
     * @var unknown
     */
    public static $http_status_code = array(
            '1000' => '系统内部错误 服务器端内部逻辑错误，请稍后重试。 500',
            '1001' => '只支持 HTTP Post 方法 不支持 Get 方法。 405',
            '1002' => '缺少了必须的参数 必须改正。 400',
            '1003' => '参数值不合法 必须改正。 400',
            '1004' => '验证失败 必须改正，详情请看：调用验证。 401',
            '1005' => '消息体太大通知 “iOS”:{ } 内的总体长度不超过：220 个字节（包括自定义参数和符号）。JPush 的 消息加通知 部分长度不超过 1K 字节。',
            '1008' => 'app_key 参数非法 必须改正。 400',
            '1011' => '没有满足条件的推送目标 请检查audience 400',
            '1020' => '只支持 HTTPS 请求	必须改正。 404',
            '1030' => '内部服务超时 稍后重试'
    );


    /**
     * 标签设置方面状态码
     * @var unknown
     */
    public static $tags_status_code = array(
            '7000' => '内部错误, 系统内部错误, 500',
            '7001' => '校验信息为空, 必须改正, 详情请看：调用验证。401',
            '7002' => '请求参数非法, 必须改正, 400',
            '7004' => '校验失败'
    );

    public static $receiver_type = array(
            '7000' => '内部错误, 系统内部错误, 500',
            '7001' => '校验信息为空, 必须改正, 详情请看：调用验证。401',
            '7002' => '请求参数非法, 必须改正, 400',
            '7004' => '校验失败'
    );

    /**
     * 标签类型
     * @var unknown
     */
    public static $tag_type = array(
            'business_hall' => '营业厅',
            'province'      => '省',
            'city'          => '市',
            'area'          => '区',
            'phone_name_version' => '品牌型号',
    );

    /**
     * 推送 title 参数值 的定义
     * @var unknown
     */
    public static $push_title_type = array(
            '2'     => '内容推送',
            '101'   => '版本升级推送',
    );
}
?>