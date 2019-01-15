<?php
/**
  * alltosun.com 获取数据 get.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年5月8日 下午7:15:17 $
  * $Id$
  */
class Action
{
    public $ondev_url = 'http://201512awifi.alltosun.net/rfid/api/put';
    public function index() {


        if (!ONDEV) {
            curl_post($this->ondev_url, $_POST);
            exit;
        }

        //存储
        //p($_POST);
        $data = json_encode($_POST);

        _model('rfid_data_test')->create(array('data' => $data));
        //global $mc_wr;
        //$mc_wr->set('wangjf_data', $request_data, 60);

    }
}

?>