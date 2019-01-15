<?php

/**
* alltosun.com ajax.php
================================================
* 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
* 网站地址: http://www.alltosun.com
* ----------------------------------------------------------------------------
* 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
* ============================================================================
* @author: 祝利柯 (zhulk@alltosun.com)
* @date:2015-4-28
* $$Id$$
*/

class Action
{
    /**
     * 获取某个省下的城市
     */
    public function get_city_list()
    {
        $pid = tools_helper::post('pid', 0);
        if (!$pid) {
            return array('info'=>"参数有误");
        }

        $list = business_hall_helper::get_city_list($pid);

        if ($list) {
            return array('info'=>'ok', 'list'=>$list);
        }

        return array('info'=>'结果为空');
    }

    /**
     * 检测频道号
     */
    public function check_channel()
    {
        $channel_id = tools_helper::post('channel', "");

        if (!$channel_id) {
            return array('info'=>'频道号不能为0');
        }

        $channel_info = _uri('business_hall', array('channel'=>$channel_id));
        if ($channel_info) {
            return array('info'=>'频道已经存在');
        } else {
            return array('info'=>'ok');
        }
    }

    /**
     * 京东商品编号ajax方法
     * @return multitype:string Ambigous <Ambigous, multitype:, multitype:unknown > |string
     */
    public function get_area_list()
    {
        $pid = tools_helper::post('pid', 0);
        $list = screen_helper::get_area_list($pid);
        if ($list) {
            return array('info'=>'ok', 'list'=>$list);
        }
        return 'error';
    }

}
?>