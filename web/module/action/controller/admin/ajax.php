<?php
/**
 * alltosun.com
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 雷健雄 (leijx@alltosun.com) $
 * $Date: 2016-7-25 下午4:31:34 $
 * $Id: $
 */
class Action
{
    public function get_city_list()
    {
        $province_id = Request::getParam('province_id', 0);

        $ret = array(
                'debug' => array($province_id),
                'list'  => array()
            );

        if ($province_id) {
            $ret['list'] = _model('city')->getList(array('province_id'=>$province_id));
        }

        return $ret;
    }

}
?>