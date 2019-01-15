<?php
/**
  * alltosun.com 接收端 receive.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年6月13日 上午11:23:25 $
  * $Id$
  */
class Action
{

    /**
     * 山脊标签数据接收
     */
    public function shanj()
    {
        $data = file_get_contents("php://input");

        if (!$data) {
            $this->return_data(1001, '数据不能为空');
        }

        $data = json_decode($data, true);

        if (!$data) {
            $this->return_data(1002, '无效的数据格式');
        }

        if (count($data) != count(rfid_config::$receive_fields['shanj'])) {
            $this->return_data(1003, "数据不完整");
        }

        //验证字段
        $diff = array_diff(array_keys($data), rfid_config::$receive_fields['shanj']);

        if ($diff) {
            $this->return_data(1004, "无效的数据字段“{$diff[0]}”");
        }

        _model('rfid_shanj_original')->create($data);

        $this->return_data(0, 'success');
    }

    /**
     * 接口数据返回
     * @param unknown $code 返回码
     * @param unknown $message 错误信息
     * @param array $info  返回数据
     */
    public function return_data($code, $message, $info = array())
    {
        $return_data = array(
                        'code'      => $code,
                        'message'   => $message,
                        'result' => array()
        );

        if ($info) {
            $return_data['result'] = $info;
        }

        $return_data = json_encode($return_data, JSON_UNESCAPED_UNICODE);


        exit($return_data);
    }
}