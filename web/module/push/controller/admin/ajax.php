<?php
/**
 * alltosun.com
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com

 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宋志宇 (songzy@alltosun.com) $
 * $Date: 2017年10月25日 上午11:19:33 $
 * $Id$
 */

class Action
{



    public function get_info_by_cityname()
    {
        $key_word = Request::Get('term','');

        if (!$key_word) {
            return '数据不存在';
        }

        $city_list = _model('city')->getList(
                array(
                        'name LIKE' => "{$key_word}%"
                )
        );

         $list=array();
        foreach ($city_list as $k=> $v)
        {
            $arr=array(
                    'id'=>$v['id'],
                    'label'=>$v['name']

            );
            $list[] =$arr;
        }
        if ($list) {
            exit(json_encode($list));
        }
    }


    public function get_info_by_areaname()
    {
        $key_word = Request::Get('term','');

        if (!$key_word) {
            return '数据不存在';
        }

        $area_list = _model('area')->getList(
                array(
                        'name LIKE' => "{$key_word}%"
                )
        );

        $list=array();
        foreach ($area_list as $k=> $v)
        {
            $arr=array(
                    'id'=>$v['id'],
                    'label'=>$v['name']

            );
            $list[] =$arr;
        }
        if ($list) {
            exit(json_encode($list));
        }
    }

    public function get_imei_field()
    {
        $key_word = Request::Get('term','');

        if (!$key_word) {
            return '数据不存在';
        }

        $name = _model('screen_device')->getFields(
                'imei',
                array(
                        'imei LIKE' => "{$key_word}%"
                )
        );

        if ($name) {
            exit(json_encode($name));
        }
    }

    /**
     * 推送
     */
    public function push_by_tag()
    {
        $tag        = tools_helper::Post('tag', '');
        $push_type  = tools_helper::Post('push_type', '');
        if (!$tag) {
            return array('info' => 'fail', 'msg' => '推送标签不能为空');
        }

        if (empty(push_config::$push_title_type[$push_type])) {
            return array('info' => 'fail', 'msg' => '推送类型不存在');
        }

        //推送版本升级
        push_helper::push_msg((string)$push_type, array(), array($tag));

        return 'ok';
    }
}