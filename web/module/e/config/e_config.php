<?php
/**
  * alltosun.com 移动端模块配置wen'jian e_config.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2016-8-18 上午11:04:41 $
  * $Id$
  */
class e_config
{
    /**
     * 折线图表统计的天数
     */
    public static $stat_max_date_by_line = 15;

    /**
     * 折线图表统计的天数(小程序)
     */
    public static $app_stat_max_date_by_line = 5;

///////added by guojf start for qydev;

    //统计菜单
    public static $menu_res_name = array(
            1 => 'faq',
            2 => 'data',
            3 => 'msg'

    );

    /*
     * 微信端  特殊的登录账号
     */
    public static $user_name = array(
            0 => 'oto_01',       //李翔
            1 => 'O2O_02',       //宋子莫
            2 => 'O2O_03',       //付佳
            3 => 'O2O_04',       //宁哥
            4 => 'sherry_ma927', //马岩岩
            5 => 'jt_yyt1',      //徐处
            6 => 'jt_yyt',       //赵思源
            7 => 'jt_yyt03',     //方春辉
            8 => 'O2O_05',       //guojf
//          换到新的企业号添加账号
            9 => 'O2O_09',
            10 => 'O2O_10',
            11 => 'O2O_01',
    );

    //微信企业号成员ID
    public static $touser = array(
//             0 => 'oto_01',       //李翔
//             1 => 'O2O_02',       //宋子莫
//             2 => 'O2O_03',       //付佳
//             3 => 'O2O_04',       //宁哥
//             4 => 'sherry_ma927', //马岩岩
//             5 => 'jt_yyt1',      //徐处
//             6 => 'jt_yyt',       //赵思源
//             7 => 'jt_yyt03',     //方春辉
//             8 => 'O2O_05',       //guojf
//             //          换到新的企业号添加账号
//             9 => 'O2O_09',

            10 => 'nike_nb',
//             11 => 'O2O_10',
            11 => 'alltosun_01'
    );


    //微信企业号的部门ID
    public static $toparty = array(
            0 => '12',
    );

    //省公司帐号
    public static $pro_user_name = array(
//     安徽
        'anhui_YYT'        => 'AH',
//     北京
        'biejing_YYT'      => 'BJ',
//     福建
        'fujian_YYT'       => 'FJ',
//     甘肃
        'gansu_YYT'        => 'GS',
//     广东
        'guangdong_YYT'    => 'GD',
//     广西
        'guangxi_YYT'      => 'GX',
//     贵州
        'guizhou_YYT'      => 'GZ',
//     海南
        'hainan_YYT'       => 'HI',
//     河北
        'hebei_YYT'        => 'HB',
//     河南
        'heinan_YYT'       => 'HA',
//     黑龙江
        'heilongjiang_YYT' => 'HL',
//     湖北
        'hubei_YYT'        => 'HE',
//     湖南
        'hunan_YYT'        => 'HN',
//     吉林
        'jilin_YYT'        => 'JL',
//     江苏
        'jiangsu_YYT'      => 'JS',
//     江西
        'jiangx_YYT'       => 'JX',
//     辽宁
        'liaoning_YYT'     => 'LN',
//     内蒙古
        'neimenggu_YYT'    => 'NM',
//     宁夏
        'ningxia_YYT'      => 'NX',
//     青海
        'qinghai_YYT'      => 'QH',
//     山东
        'shandong_YYT'     => 'SD',
//     山西
        'shanxi_YYT'       => 'SX',
//     陕西
        'shanxi_YYT'       => 'SN',
//     上海
        'shanghai_YYT'     => 'SH',
//     四川
        'sichuan_YYT'      => 'SC',
//     天津
        'tianjin_YYT'      => 'TJ',
//     西藏
        'xizang_YYT'       => 'XZ',
//     新疆
        'xinjiang_YYT'     => 'XJ',
//     云南
        'yunnan_YYT'       => 'YN',
//     浙江
        'zhejiang_YYT'     => 'ZJ',
//     重庆
        'chongqing_YYT'    => 'CQ'
    );
///////////end

    /**
     * 第三方单点登录信息
     * sjdt -> 数据地图,
     *  文档：http://market-mng-dev.obaymax.com/swagger-ui.html
     * @var unknown
     */
    public static $to_login_info = array(
            'szdt' => array(
                    'app_id'  => '9777880925419479',
                    'app_key' => '3j4vwhA82tXnNVGrTIobKqGOJnkxJrCl',
                    'url'     => ' http://dm.pzclub.cn/api/awifi/login?username={USERNAME}&token={TOKEN}&appId=9777880925419479&web={WEB}'
            )
    );

    /**
     * 企业号申请所属部门
     * key 为企业号的对应ID
     */
    public static $qydev_apply_depart = array(
            '2'   => '数字地图',
//             '4'   => '集团公司',
            '145' => '爱WIFI',
//             '6'   => 'O2O事业部',
            '147' => 'ibeacon',
            '211' => '一体化排队',
    );

}