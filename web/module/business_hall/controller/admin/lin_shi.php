<?php
/**
 * alltosun.com  lin_shi.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-1-11 下午6:00:42 $
 * $Id$
 */
class Action
{
    public function index()
    {
        //拿出1162个没有绑定
        $user_number = _model('business_s')->getFields( 'user_number' , array('diff' => 1));
        
        //取出这些数据
        $data        = _model('business_hall')->getList( array('user_number' => $user_number));
        
        $num = 0;
        
        foreach ($data as $v) {
            _model('business_s')->update(array('user_number' => $v['user_number']), array('diff' => 2));
            ++$num;
        }
        
        p($num);
    }
    public function no_yyt()
    {
        
        //取出这些数据    is_true 1位默认 2为已经更新business_hall is_bounding=2 0为 business_hall is_bounding=1的
        $data        = _model('business_s')->getList( array('diff' => 1 , 'is_true' => 1)); //剩下的表里没有的渠道码
        
        foreach ($data as $k=>$v) {
            $list[$k]['user_number']  = $v['user_number'];
        }
        
        $params['data']     = $list;
        $params['head']     = array('营业厅渠道码');
        
        Csv::getCvsObj($params)->export();
    }
}