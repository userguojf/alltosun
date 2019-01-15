<?php
/**
 * alltosun.com  ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-9-14 下午4:35:00 $
 * $Id$
 */
class Action
{
    private $user_id = 0;


    public function get_business_hall_list()
    {
        $key_word = Request::Get('term', '');

        if (!$key_word) {
            return '数据不存在';
        }

        $business_hall_list = _model('business_hall')->getFields(
           'title',
	       array(
                'title LIKE' => "{$key_word}%"
            )
        );

        if ($business_hall_list) {
            exit(json_encode($business_hall_list));
        }
    }
}