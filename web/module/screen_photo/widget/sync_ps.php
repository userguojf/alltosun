<?php
/**
 * alltosun.com 合图 sync_ps.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-1-18 上午10:10:36 $
 * $Id$
 */

class sync_ps_widget
{
    // 
    public function ps()
    {
        set_time_limit(0);

        $table = 'screen_content_set_meal';

        $filter = [];
        $filter['status'] = 0;
        $limit = " LIMIT 10 ";

        $list = _model($table)->getList($filter);
// p($list);exit();
        if ( !$list ) return '暂无合成图信息';

        foreach ($list as $k => $v) {
            $link = screen_photo_helper::screen_ps($v);

            if ( !$link ) {
                // 失败
                _model($table)->update($v['id'], array('status' => 2));
            } else {
                // 成功
                _model($table)->update($v['id'],
                    array(
                        'link'   => $link,
                        'status' => 1
                    )
                );
            }
        }

        return '完成本次任务';
    }
}