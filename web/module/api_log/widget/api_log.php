<?php

/**
* alltosun.com 接口统计 api_log.php
* ============================================================================
* 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
* 网站地址: http://www.alltosun.com
* ----------------------------------------------------------------------------
* 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
* ============================================================================
* $Author: 钱有明 (qianym@alltosun.com) $
* $Date: 2015-7-3 上午10:05:33 $
* $Id$
*/

class api_log_widget
{
    /**
     * 保存接口请求log
     * @param array $info
     * @return int
     */
    public function record($info)
    {
        if (!$info) {
            return 0;
        }

        return _model('api_log')->create($info);
    }

    /**
     * 更新接口请求log
     * @param int $id
     * @param array $info
     * @return boolean
     */
    public function update_record($id, $info)
    {
        if (!$id || !$info) {
            return false;
        }

        return _model('api_log')->update($id, $info);
    }
}
?>