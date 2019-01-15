<?php

/**
 * alltosun.com 附件操作 attachment_relation.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-10-13 下午5:34:28 $
 * $Id$
*/

class attachment_relation_model extends Model
{
    //attachment_relation 数据字段    res_id, res_type, attachment_id
    //attachment          数据字段    id,  path

    /**
     * 添加关系
     * @param $res_id
     * @param $res_type
     * @param $attachment_id
     */
    function add_relation($res_id, $res_name, $attachment_id)
    {
        return parent::create(array('res_id'=>$res_id, 'res_name'=>$res_name, 'attachment_id'=>$attachment_id), 'REPLACE');
    }

    /**
     * 删除关系
     * @param $res_id
     * @param $res_type
     * @param $attachment_id
     */
    function delete_relation($res_id, $res_name)
    {
        return parent::delete(array('res_id' => $res_id, 'res_name' => $res_name));
    }

    /**
     * 获取 res_id 的所有附件
     * @param $res_id
     * @param $res_type
     */
    function get_attachment($res_id, $res_name)
    {
        $list = $this->getList(array('res_id'=>$res_id, 'res_name'=>$res_name));
        $tem_arr = array();
        foreach ($list as $v) {
            $tem_arr[] = _model('attachment')->read($v['attachment_id']);
        }
        return $tem_arr;
    }
}
?>