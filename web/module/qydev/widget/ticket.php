<?php
/**
 * alltosun.com jsapi_ticket是企业号号用于调用微信JS接口的临时票据 ticket.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-7-28 上午11:54:34 $
 * $Id$
 * guojf copied `token` writing style
 */

class ticket_widget
{
    private $mc_jsapi_ticket_key = 'qydev_jsapi_ticket';
    private $db_jsapi_ticket_id  = 0;
    private $jsapi_ticket        = '';
    private $access_token        = '';

    public function __construct()
    {
        $this->access_token = _widget('qydev.token')->get_access_token('work');
    }

    /**
     *  获取企业号jsapi_ticket
     *  获取方式 缓存 -> DB -> 网络请求
     *  @return string jsapi_ticket
     */
    public function get_jsapi_ticket()
    {
        $this->get_mc_jsapi_ticket() || $this->get_db_jsapi_ticket() || $this->get_socket_jsapi_ticket();

        return $this->jsapi_ticket;
    }

    private function get_mc_jsapi_ticket()
    {
        global $mc_wr;

        $this->jsapi_ticket = $mc_wr->get($this->mc_jsapi_ticket_key);

        return  !!$this->jsapi_ticket;
    }

    private function get_db_jsapi_ticket()
    {
        $jsapi_ticket_info = _model('qydev_jsapi_ticket')->read(array(1=>1));

        if (!$jsapi_ticket_info) {
            return false;
        }

        $this->db_jsapi_ticket_id  = $jsapi_ticket_info['id'];

        if (strtotime($jsapi_ticket_info['expire_time']) < time()) {
            return false;
        }

        $this->jsapi_ticket        = $jsapi_ticket_info['jsapi_ticket'];

        return true;
    }

    private function set_mc_jsapi_ticket($info)
    {
        global $mc_wr;

        return $mc_wr->set($this->mc_jsapi_ticket_key, $info['ticket'], $info['expires_in']);
    }

    private function set_db_jsapi_ticket($info)
    {
        $data['jsapi_ticket'] = $info['ticket'];
        $data['expire_time']  = date('Y-m-d H:i:s', time() + $info['expires_in']);

        if ($this->db_jsapi_ticket_id > 0) {
            _model('qydev_jsapi_ticket')->update($this->db_jsapi_ticket_id, $data);
        } else {
            _model('qydev_jsapi_ticket')->create($data);
        }

        return true;
    }

    /**
     * 调用接口获取jsapi_ticket
     */
    public function get_socket_jsapi_ticket()
    {
        if (!$this->access_token) {
            return false;
        }

        $url = qydev_config::$get_jsapi_ticket_url.'access_token='.$this->access_token;

        $info = json_decode(curl_get($url),true);

        if ( isset($info['errcode']) && !$info['errcode'] ) {
            $this->set_mc_jsapi_ticket($info);
            $this->set_db_jsapi_ticket($info);

            $this->jsapi_ticke = $info['ticket'];

            return !!$this->jsapi_ticke;
        }

        return false;
    }
}