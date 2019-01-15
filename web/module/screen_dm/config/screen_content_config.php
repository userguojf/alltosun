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
class screen_content_config
{
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
            '/2018/01/15/20180115100237000000_1_17130_1.jpg',
            '/2017/12/22/15139321791631.jpg',
            '/2017/12/17/20171217002806000000_1_49145_19.jpg'
    );

    public static $content_meal_type = array(
        '1' => '新建详情页',
        '2' => '外部链接'
    );
}