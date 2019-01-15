<?php

/**
* alltosun.com action_test.php
================================================
* 版权所有 (C) 2009-2014 北京互动阳光科技有限公司，并保留所有权利。
* 网站地址: http://www.alltosun.com
* ----------------------------------------------------------------------------
* 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
* ============================================================================
* @author: 祝利柯 (zhulk@alltosun.com)
* @date:2015-2-26
* $$Id: action_test.php 246459 2015-05-25 11:36:06Z leijx $$
*/

class Action
{
    public function __call($action='', $params=array())
    {
        echo 'test';
        echo '<hr />';
        $result = array();
        $ids = array();
        // 取出所有URL
        $list = _model('action')->getList(array('pid !='=>0));
        //p($list);
        foreach ($list as $k=>$v) {
            $ids[$k]['id'] = $v['id'];
            $result[$k]['url'] = $v['url'];
        }

        echo 'ok';
    }

    public function test()
    {
        $ids = array('hello','world');
        $fields = implode('","', $ids);
        p($fields);
    }

    public function get_action_list_by_user_id()
    {
        $user_id = tools_helper::get('user_id', 0);
        $r = action_helper::user_action_list($user_id);
        p($r);
    }
}
?>