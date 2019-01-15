<?php

/**
 * alltosun.com  menu.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-4-7 上午11:25:04 $
 * $Id$
 */


/**
 * 注意：这里修改要按照文档
 */
class menu_widget
{
    private $access_token = NULL;

    /**
     * 自定义菜单-创建
     * @$params   array 创建菜单数据包data键值 和企业应用的id键值 参数详情查看企业号文档
     * return     {"errcode":0,"errmsg":"ok"} 请求成功，创建完成
     */
    public function create($params)
    {
        if ( !$params['agent_id'] ) return '规定：企业应用的id为整型';
        if ( !$params['data'] ) return '数据不能为空';

        $this->access_token = _widget('wework.token')->get_access_token($params['agent_id']);

        //接口地址
        $api_url = wework_config::$menu_create_url.'access_token='.$this->access_token.'&agentid='.$params['agent_id'];
        //请求接口
        $json_info = curl_post($api_url , $params['data']);

//         $info      = json_decode($json_info , true);

        echo $json_info;
    }

    // 以下是企业号
    /**
     * 自定义菜单-删除
     * @$agent_id 企业应用的id，整型。可在应用的设置页面查看
     * return     {"errcode":0,"errmsg":"ok"} 请求成功，删除完成
     */
    public function menu_delete($agent_id)
    {
        //提示 '1' 和  1 的区别
        if ($agent_id) return '规定：企业应用的id为整型';

        //接口地址
        $api_url = qydev_config::$menu_delete_url.'access_token='.$this->access_token.'&agentid='.$agent_id;
        //请求接口
        $json_info = curl_get($api_url);

        $info      = json_decode($json_info , true);

        if (!isset($info['errmsg']) || $info['errmsg'] != 'ok') {
            return false;
        }

        return $json_info;
    }

    /**
     * 自定义菜单-获取列表
     * @$agent_id 企业应用的id，整型。可在应用的设置页面查看
     * return     返回结果与菜单创建的参数一致
     */
    public function menu_get($agent_id)
    {
        //提示 '1' 和  1 的区别
        if (!is_int($agent_id)) {
            return '规定：企业应用的id为整型';
        }

        if (!$this->access_token) {
            return 'access_token获取失败';
        }

        //接口地址
        $api_url = qydev_config::$menu_get_url.'access_token='.$this->access_token.'&agentid='.$agent_id;
        //请求接口
        $json_info = curl_get($api_url);

        $info      = json_decode($json_info , true);

        return $info;
    }
}