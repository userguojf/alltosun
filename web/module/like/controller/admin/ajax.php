<?php
/**
 * alltosun.com  ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 赵高举 (zhaogj@alltosun.com) $
 * $Date: 2018-4-3 下午5:35:51 $
 * $Id$
 */
class Action
{
    /**
     * ajax 执行审核和软删除
     * @return string
     */
    public function update_res_examine()
    {
        $id = Request::Post('id', 0);
        $status = Request::Post('status', 0);

        if (!$id) return '信息错误';

        $info = _uri('comment', $id);

        if (!$info) return '信息不存在';

        if ($status == 2) {
            // 执行软删除
            _model('comment')->update($id, array('is_del' => 1));
        } else {
            // 是否审核
            _model('comment')->update($id, array('examine' => $status));
        }

        return 'ok';
    }
}