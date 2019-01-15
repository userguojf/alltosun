<?php
/**
 * alltosun.com  token.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-5-2 下午7:23:25 $
 * $Id$
 */

class token_widget
{
    private $agent_id            = '';
    private $mc_access_token_key = 'wework_access_token';
    private $db_access_token_id  = 0;
    private $access_token        = '';

    public function get_access_token($agent_id)
    {
        // 应用ID
        $this->agent_id = $agent_id;
        // 缓存每个应用的唯一下标
        $this->mc_access_token_key = $this->mc_access_token_key . '_' . $this->agent_id;

        $this->get_mc_access_token() || $this->get_db_access_token() || $this->get_socket_access_token();

        return $this->access_token;
    }

    private function get_mc_access_token()
    {
        global $mc_wr;

        $this->access_token = $mc_wr->get($this->mc_access_token_key);

        return !!$this->access_token;
    }

    private function get_db_access_token()
    {
        $token_info = _model('wework_access_token')->read(array('agent_id' => $this->agent_id));

        if ( !$token_info ) return false;

        $this->db_access_token_id = $token_info['id'];
        $this->access_token       = $token_info['access_token'];

        if ( strtotime($token_info['expire_time']) < time() ) return false;

        return true;
    }

    public function get_socket_access_token()
    {
        $request_url = qydev_config::$gettoken_url;

        $url = $request_url.'corpid='.qydev_config::$corp_id.'&corpsecret='.qydev_config::$agent_secret[$this->agent_id];

        $info = json_decode(curl_get($url),true);

        if ( isset($info['errcode']) && !$info['errcode'] ) {
            // 设置缓存
            $this->set_mc_access_token($info);
            // 存表
            $this->set_db_access_token($info);

            $this->access_token = $info['access_token'];

            return true;
        }

        return false;
    }

    private function set_mc_access_token($info)
    {
        global $mc_wr;

        return $mc_wr->set($this->mc_access_token_key, $info['access_token'], $info['expires_in']);
    }

    private function set_db_access_token($info)
    {
        $data = [];
        $data['agent_id']    = $this->agent_id;
        $data['access_token']       = $info['access_token'];
        $data['expire_time'] = date('Y-m-d H:i:s', time() + $info['expires_in']);

        if ( $this->db_access_token_id > 0 ) {
            _model('wework_access_token')->update($this->db_access_token_id, $data);
        } else {
            _model('wework_access_token')->create($data);
        }

        return true;
    }

}