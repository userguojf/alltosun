<?php
/**
 * alltosun.com 主页面 ajax.php
 * ============================================================================
 * 版权所有 (C) 2009-2018 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 赵高举 (zhaogj@alltosun.com) $
 * $Date: 2018/4/20 10:06 $
 * $Id$
 */

class Action
{
    public function get_title_field()
    {
        $key_word = Request::Get('term','');

        if (!$key_word) {
            return '数据不存在';
        }

        $name = _model('business_hall')->getFields(
            'title',
            array(
                'title LIKE' => "%{$key_word}%"
            )
        );

        if ($name) {
            exit(json_encode($name));
        }
    }
}