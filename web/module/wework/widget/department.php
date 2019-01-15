<?php
/**
 * alltosun.com 企业号部门管理 department.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 申小宁 (shenxn@alltosun.com) $
 * $Date: 2017-4-7 上午11:15:50 $
 * $Id$
 */

class department_widget
{
    private $access_token = '';

    public function __construct()
    {
        $this->access_token = _widget('wework.token')->get_access_token('work');

        if (!$this->access_token) {
            return false;
        }
    }

    /**
     * 获取部门列表
     * @return array
     */
    public function get_department_list($params = array())
    {

        $url = qydev_config::$get_department_list_url."access_token=".$this->access_token;

        if (isset($params['id']) && $params['id']) {
            $url .= "&id=".$params['id'];
        }

        $json_info = curl_get($url);
        $info      = json_decode($json_info,true);

        if (!isset($info['errmsg']) || $info['errmsg'] != 'ok') {
            return false;
        }

        return $info['department'];
    }


    /*
     * 创建部门
     */
    public function create_department($params)
    {
        if (!is_array($params)) {
            return '请传数组形式的参数';
        }

        if (!isset($params['name']) || !$params['name']) {
            return false;
        }

        if (!isset($params['pid']) || !$params['pid']) {
            return false;
        }

        $url = qydev_config::$create_department_url.'access_token='.$this->access_token;

        $data = '{
                   "name"     : "'.$params['name'].'",
                   "parentid" : '.$params['pid'].'
                }';

        $result = json_decode(curl_post($url, $data) ,true);

        if (isset($result['errmsg']) && $result['errmsg'] != 'created' ) {
            return array('errcode' => 1, 'errmsg' => $result);
        }

        return $result;
    }
}