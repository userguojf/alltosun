<?php
/**
  * alltosun.com 数字地图操作 dm.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年8月24日 下午3:24:14 $
  * $Id$
  */
class Action
{

    public function query()
    {
        if (ONDEV) {
            $url = 'http://market-mng-dev.obaymax.com/awifi/shoppe/query';
        } else {
            $url = 'http://dm.pzclub.cn/api/awifi/shoppe/query';
        }

        $user_number    = tools_helper::Get('user_number', '1101021002051');
        $shoppe_id      =  tools_helper::Get('shoppe_id', 0);
        $time           = time();

        $params = array(
                'appid'         => shoppe_config::$dm_api_config['appid'], // 专柜名称 ,
                'timestamp'     => $time,
                'token'         => shoppe_helper::generate_dm_api_token($time),
                'channelCode'   => $user_number, //渠道编码 ,
        );

        if ($shoppe_id) {
            $params['shoppeId'] = $shoppe_id;
        }

        $result = shoppe_helper::dm_curl_post($url, json_encode($params));

        p($result);

    }
}