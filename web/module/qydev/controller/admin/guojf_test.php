<?php
/**
 * alltosun.com  guojf_test.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-4-19 上午9:53:17 $
 * $Id$
 */

class Action
{
    public function delete_department()
    {
        _model('public_contact_department')->delete(array(1 => 1));

        echo '部门清空，执行成功';
    }

    public function delete_user_info()
    {
        _model('public_contact_user')->delete(array(1 => 1));
    
        echo '通讯录成员清空，执行成功';
    }
}