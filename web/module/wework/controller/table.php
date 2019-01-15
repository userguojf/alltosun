<?php
/**
 * alltosun.com 测试方法 test.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2018-1-20 下午6:09:01 $
 * $Id$
 */

class Action
{
    public function index()
    {
        $table = array(
                'qydev_news_content',
                'qydev_news_content_answer',
                'qydev_news_content_zan_record',
                'qydev_news_operate_record',
                'qydev_news_share',
                'qydev_share_record'
        );

        foreach ($table as $k => $v) {
//             $sql = " TRUNCATE TABLE `{$v}` ";
            _model($v)->delete(array(1 => 1));
        }

        _model('qydev_news')->update(array(1 => 1), array(
                'reading_num' => 0,
                'zan_num' => 0,
                'content_num' => 0,
                'share_num' => 0
        ));
    }
}