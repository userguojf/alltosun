<?php
/**
 * alltosun.com  user.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-6-25 下午4:04:54 $
 * $Id$
 */
/**
 * 
 * @author 郭剑峰
 *备注:本脚本与企业号各个业务线都有联系，修改前请沟通！
 */

class user_widget
{
    private $access_token = '';

    public function __construct()
    {
        $this->access_token = _widget('qydev.token')->get_access_token();

        if (!$this->access_token) {
            //记录错误日志
            qydev_helper::record_error_log('user', '成员信息token获取失败',$access_token );
            return false;
        }
    }

    /**
     * 企业号申请申请成员信息添加的创建成员方法（验证在调用之前添加）
     * @param array $params
     * @return boolean
     */
    public function create_user_info($params)
    {
        //创建成员信息形式是目前企业号创建成员时已经固定的字段 这都是必传的字段
        $data = '{
                       "userid"     : "'.$params['user_id'].'",
                       "name"       : "'.$params['user_name'].'",
                       "department" : '.$params['depart_ids'].',
                       "mobile"     : "'.$params['user_phone'].'",
                       "weixinid"   : "'.$params['weixin_id'].'",
                       "extattr"    : {
                                            "attrs" : 
                                             [
                                                 {"name":"analog_id","value":"'.$params['analog_id'].'"},
                                                 {"name":"an_id","value":"'.$params['an_id'].'"}}
                                             ]
                  }';
//         ,
        $url  = qydev_config::$create_user_url."access_token=".$this->access_token;

        $info = json_decode(curl_post($url , $data),true);

        if (isset($info['errmsg']) && 'created' != $info['errmsg'] ) {
            return array('errcode' => 1, 'errmsg' => $info);
        }

        return $info;//成功
    }


    /**
     * 删除企业号通讯录成员信息
     * @param str $user_id
     * @return boolean
     */
    public function delete_user_info($user_id)
    {
        if (!$user_id) {
            return false;
        }

        $url  = qydev_config::$delete_user_url."access_token=".$this->access_token.'&userid='.$user_id;

        $info = json_decode(curl_get($url),true);

        if (isset($info['errmsg']) && 'deleted' != $info['errmsg'] ) {
            return array('errcode' => 1, 'errmsg' => $info);
        }

        return $info;
    }

    /*
     * 更新企业号通讯录成员信息(只更新部门)
     * 参数格式参考企业号接口开发文档
     */
    public function update_user_info($data)
    {
        if (!$data) {
            return '请传参数';
        }

        $url = qydev_config::$update_user_info_url.'access_token='.$this->access_token;

        $info = json_decode(curl_post($url, $data),true);

        if (isset($info['errmsg']) && $info['errmsg'] != 'updated') {
            return array('errcode' => 1, 'errmsg' => $info);
        }

        return $info;
    }

    /**
     * 获取成员接口
     * @param  string $user_id
     * @return string $result
     */
    public function get_user_info($user_id)
    {
        $url  = qydev_config::$get_user_info_url.'access_token='.$this->access_token.'&userid='.$user_id;

        $info = json_decode(curl_get($url), true);

        if (isset($info['errmsg']) && $info['errmsg'] != 'ok') {
             return array('errcode' => 1, 'errmsg' => $info);
        }

        return $info;
    }

    /**
     * 获取该部门下的成员信息
     * @param unknown $department_id
     * @param number $fetch_child
     * @param number $status
     * @return boolean|mixed
     */
    public function get_department_user_info($department_id,$fetch_child = 1,$status = 0)
    {
        if (!$department_id) return false;

        $url = qydev_config::$get_department_user_url.'access_token='.$this->access_token.'&department_id='.$department_id.'&fetch_child='.$fetch_child.'&status='.$status;

        $info = json_decode(curl_get($url) , true);

        if (isset($info['errmsg']) && $info['errmsg'] != 'ok' ) {
            return array('errcode' => 1, 'errmsg' => $info);
        }

        return $info;
    }
    

    /*完成版本的该企业号的全部字段
     *  $data = '{
    "userid"     : "'.$params['user_id'].'",
    "name"       : "'.$params['name'].'",
    "department" : '.$params['department_ids'].' ,
    "position"   : "'.$params['position'].'",
    "mobile"     : "'.$params['phone'].'",
    "gender"     : "'.$params['sex'].'",
    "email"      : "'.$params['email'].'",
    "weixinid"   : "'.$params['weixinid'].'",
    "extattr"    : {
    "attrs" :
    [
    {"name":"英文名","value":"'.$params['egligsh_name'].'"},
    {"name":"座机","value":"'.$params['desk_phone'].'"},
    {"name":"虚拟账号","value":"'.$params['vr_id'].'"},
    {"name":"analog_id","value":"'.$params['analog_id'].'"},
    {"name":"an_id","value":"'.$params['an_id'].'"}}
    ]
    }';*/
}