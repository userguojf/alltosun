<?php
/**
  * alltosun.com 推送日志 log.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2017年10月17日 上午11:33:37 $
  * $Id$
  */
class Action
{
    public $per_page = 20;
    public function __call($action="", $params=array())
    {

        $page           = tools_helper::Get('page_no', 1);
        $response_code  = tools_helper::Get('response_code', '');
        $filter         = array();

        if ($response_code) {
            if ($response_code == -1) {
                $filter['response_code !='] = 200;
            } else {
                $filter['response_code'] = $response_code;
            }

        }

        if (!$filter){
            $filter = array(
                    '1' => 1
            );
        }

        $log_list = get_data_list('push_log', $filter, ' ORDER BY `id` DESC', $page, $this->per_page);

        Response::assign('log_list', $log_list);
        Response::assign('response_code', $response_code);
        Response::display('admin/log/log_list.html');
    }
}