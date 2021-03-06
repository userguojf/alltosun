<?php
/**
  * alltosun.com 亮屏内容config screen_content_config.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年10月17日 下午4:20:19 $
  * $Id$
  */
class screen_content_new_config
{
    /**
     * screen_content 数据表
     * status字段的状态
     *
     */
    public static $field_status = array(
            0 => '默认',
            1 => '发布',
            2 => '删除',
    );

    /**
     * 焦点图分类
     */
    public static $content_put_type = array(
            'group'         => '全国投放',
            'province'      => '全省投放',
            'city'          => '城市投放',
            'area'          => '区县投放',
            'business_hall' => '营业厅投放'
    );

    /**
     * 投放者分类
     */
    public static $content_issuer_res_name_type = array(
            'group'         => '集团投放',
            'province'      => '省级投放',
            'city'          => '市级投放',
            'area'          => '县级投放',
            'business_hall' => '厅级投放'
    );

    public static $search_type = array(
            0 => '全部',
            1 => '已上线',
            4 => '已下线',
            2 => '已过期',
            3 => '未开始',
    );

    public static $put_type = array(
            1 => array(
                    0 => '仅保存',
                    1 => '集团投放',
                    2 => '部分投放'
            ),
            2 => array(
                    0 => '仅保存',
                    1 => '全省投放',
                    2 => '部分投放'
            ),
            3 => array(
                    0 => '仅保存',
                    1 => '全市投放',
                    2 => '部分投放'
            ),
            4 => array(
                    0 => '仅保存',
                    1 => '全区投放',
                    2 => '部分投放'
            ),
            5 => array(
                    0 => '仅保存',
                    1 => '立即投放'
            )
    );

    public static $put_meal_type = array(
        1 => array(
            0 => '仅保存',
            1 => '集团投放',
            2 => '部分投放'
        ),
        2 => array(
            0 => '仅保存',
            1 => '全省投放',
            2 => '部分投放'
        ),
        3 => array(
            0 => '仅保存',
            1 => '全市投放',
            2 => '部分投放'
        ),
        4 => array(
            0 => '仅保存',
            1 => '全区投放',
            2 => '部分投放'
        ),
        5 => array(
            0 => '仅保存',
            1 => '立即投放',
            2 => '部分投放'
        )
    );

    public static $content_type = array(
            '1' => '图片',
            '2' => '视频',
            '3' => '链接',
            '4' => '机型宣传图',
            '5' => '套餐图'
    );

    /**
     * 套餐图底图
     * @var unknown
     */
    public static $content_set_meal_img = array(
            //'/images/meal_background/20180205214300-0.png',
            '/images/meal_background/20180512124600-0.png'
    );

    public static $content_meal_type = array(
        '1' => '新建详情页',
        '2' => '外部链接'
    );

    // 付佳 2018/1/22 11:35:12这个表里标黄的不投放
    public static $except_user_number = array ( 0 => '3404001110053', 1 => '3401001113735', 2 => '3401001113737', 3 => '3411001113143', 4 => '3412001110146', 5 => '3402001110120', 6 => '3402001110121', 7 => '3501111053932', 8 => '3505211009314', 9 => '3501021053285', 10 => '3501811288040', 11 => 'FJ77_YYT', 12 => '3501031341977', 13 => '3501231000141', 14 => '3505211147555', 15 => '3501111058059', 16 => '3501041053905', 17 => '3501021000100', 18 => '3505241034495', 19 => '3501021000099', 20 => '3505811060099', 21 => '3501021371041', 22 => '3501031053944', 23 => '3501021000104', 24 => '4420001074970', 25 => '4502051104976', 26 => '5201031006358', 27 => '1304341098167', 28 => '1303041097901', 29 => '2309031047761', 30 => '2306041400940', 31 => '2312221045618', 32 => '4203241914620', 33 => '4312241011019', 34 => '2201011001641', 35 => '2201011029661', 36 => '3206841161222', 37 => '3601041032178', 38 => '2101021000510', 39 => '1501011000207', 40 => '1509011002162', 41 => '6301021000000', 42 => '6322251000001', 43 => '3704021000518', 44 => '1401001610166', 45 => '1410001605562', 46 => '1407021632559', 47 => '1401001610586', 48 => '1407261611413', 49 => '1401091632530', 50 => '1401051633070', 51 => '1401001606698', 52 => '1407021638847', 53 => 'SX62_YYT', 54 => '1407281634751', 55 => '1401001615942', 56 => 'SX64_YYT', 57 => '1401011634534', 58 => '1401001615160', 59 => '1401001609631', 60 => '1407281603055', 61 => '1407231602743', 62 => '6104031018913', 63 => '3401001113738' );
    public static $except_content_id  = 202;

    /**
     * ffmpeg 转码参数 范围是0-51 越小质量越好，文件越大，
     * 参数为18被认为视觉无损，此处设定为18
     * @author 王敬飞 (wangjf@alltosun.com)
     * @date 2018年2月13日下午2:54:43
     */
    public static $ffmpeg_crf = 18;

    /**
     * added by guojf
     * @var unknown
     */
    public static $upload_choice = [
        1 => '使用平台制作一张宣传图',
        2 => '不了，我要发布自己的宣传品'
    ];
}