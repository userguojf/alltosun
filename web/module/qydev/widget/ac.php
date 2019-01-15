<?php
/**
 * alltosun.com  
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * szy: 宋志宇 (songzy@alltosun.com)
 * 2018年4月23日: 2016-7-26 下午3:05:10
 * Id
 */
class ac_widget
{

    public function get_repal_count($res_name,$id)
    {
        $count = _model('comment')->getTotal(array('res_name'=>$res_name,'res_id'=>$id));
        return $count;
    }
   
    
    /**
     * 获取评论列表
     * @param number $pid
     * @param array $result
     * @author songzy
     */
    public function getReplist($res_id,&$result = array(),$pid = 0)
    {
        $arr = _model('comment')->getList(array('pid' => $pid,'res_id' => $res_id));
        if(empty($arr)){
            return array();
        }
        foreach ($arr as $child) {
            $thisArr=&$result[];
            $child["children"] = $this->getReplist($res_id,$thisArr,$child["id"]);
            $thisArr = $child;
        }
        return $result;
    }
    
}
