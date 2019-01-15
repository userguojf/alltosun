<?php
/**
 * alltosun.com 设备性能对比 contrast.php
 * ============================================================================
 * 版权所有 (C) 2009-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王磊 (wangl@alltosun.com) $
 * $Date: 2017-6-13 上午10:19:09 $
*/

// load func.php
probe_helper::load('func');

class contrast
{
    /**
     * 对比时间
     *
     * @var Int
     */
    private $date = 20170611;

    /**
     * 构造函数
     *
     * @return  Obj
     */
    public function __construct()
    {
        
    }

    /**
     * 分析一个设备探测到的数据
     *
     * @param   String  设备编号
     * @return  Array
     */
    public function analysis($dev)
    {
        $data = array(
            'indoor'    =>  0,
            'outdoor'   =>  0,
            'brands'    =>  array(
                'recog' =>  0,
                'norecog'=> 0
            )
        );

        $dev_info = _model('probe_device')->read(array('device' => $dev));

        if ( !$dev_info ) {
            return $data;
        }

        $db  = get_db($dev_info['business_id'], 'day');
        
        if ( !$db ) {
            return $data;
        }

        $sql  = " SELECT `mac`, `is_indoor` from `{$db -> table}` WHERE `date` = {$this->date} AND `dev` = '{$dev}' ";
        // $s = time();
        $list = $db -> getAll($sql);
        // $e = time();
        // echo 'getAll:'.($e - $s).'<br/>';
        // return $data;
        // an_dump($sql);
        // an_dump(count($list));

        // $s = time();
        foreach ($list as $k => $probe) {
            $mac = $probe['mac'];

            if ( $probe['is_indoor'] ) {
                $data['indoor'] ++;
            } else {
                $data['outdoor'] ++;
            }

            // $s = time();
            $name = probe_helper::get_brand($mac);
            // $e = time();
            // echo 'get '.$mac.' brand '.($e - $s).'<br />';

            if ( $name == '其他' ) {
                $data['brands']['norecog'] ++;
            } else {
                $data['brands']['recog'] ++;
            }
        }
        // $e = time();
        // echo 'foreach list '.($e - $s);

        return $data;
    }
}