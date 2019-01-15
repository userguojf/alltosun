<?php
/**
 * alltosun.com  ip.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王德康 (wangdk@alltosun.com) $
 * $Date: 2014-6-29 下午12:01:32 $
 * $Id$
 */
require_once 'helper/AnCurl.php';

class ip_widget
{
    /**
     * 根据客户机IP获取客户机城市
     * @return 城市名称,北京市
     */
    public function get_client_city()
    {
        $ip = Request::getClientIp();
        $curl = new AnCurl();

        $url = 'http://ip.taobao.com/service/getIpInfo.php?ip='.$ip;

        $data = $curl->exec($url, 'get', $ip);
        $data = json_decode($data, true);

        if (isset($data['code']) && $data['code'] == 0) {
            return $data['data']['city'];
        }

        $curl->close();

        return false;
    }
}
?>