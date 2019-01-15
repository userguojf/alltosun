<?php

/**
 * alltosun.com 附件操作 attachment.php
 * ============================================================================
 * 版权所有 (C) 2009-2012 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 宁海新 (ninghx@alltosun.com) $
 * $Date: 2012-10-13 下午5:34:59 $
 * $Id$
*/

class attachment_model extends Model
{
    /**
     * 返回对应资源的附件
     * @param int $res_id
     * @param int $res_type
     * @return array 2维数组
     */
    public function get_list($res_id, $res_name)
    {
        if (!$res_id || !$res_name) {
            return array();
        }
        $att_ids = _model('attachment_relation')->getList(array('res_id'=>$res_id, 'res_name'=>$res_name));
        $att_arr = array();
        foreach ($att_ids as $v) {
            $att_arr[] = _uri('attachment', $v['attachment_id']);
        }
        return $att_arr;
    }

    public function create($array, $app = null)
    {
        $id = parent::create($array, $app);

        //$attachment_res_type = get_res_type('attachment');
        //_widget('ip')->record($id, $attachment_res_type);

        return $id;
    }
}
?>