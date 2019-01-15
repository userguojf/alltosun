<?php
/**
 * alltosun.com  test.php
 * ============================================================================
 * 版权所有 (C) 2009-2015 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明: 这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 王磊 (wangl@alltosun.com) $
 * $Date: 2017-6-12 上午11:08:26 $
*/
probe_helper::load('func', 'func');
probe_helper::load('storage', 'trait');

class Action
{
    use storage;
    /**
     * 测试新版按天统计
     *
     * @return  Obj
     */
    public function index()
    {
        require MODULE_PATH.'/probe/core/base/business.php';

        $business = new business(46120);

        an_dump($business -> day_stat(20170611, true));
    }

    /**
     * 设备性能对比
     *
     * @return  String
     */
    public function contrast()
    {
        require __DIR__.'/contrast.php';

        $contrast = new contrast();

        $devs = array('20:28:10:00:33:00', 'a0:20:a6:0c:c2:8d', '16120801','863075030525198');

        $table = '<table border="1" cellpadding="0" cellspacing="0" style="text-align: center; width: 600px;"><tbody><tr><th>设备</th><th>室内</th><th>室外</th><th>可识别设备</th><th>不可识别设备</th></tr>';

        foreach ($devs as $k => $dev) {
            $data = $contrast -> analysis($dev);

            $table .= '<tr><td>'.$dev.'</td><td>'.$data['indoor'].'</td><td>'.$data['outdoor'].'</td><td>'.$data['brands']['recog'].'</td><td>'.$data['brands']['norecog'].'</td></tr>';
        }
        $table .= '</tbody></table>';

        echo $table;
    }


    public function mac_like()
    {
        $str1 = 'AB1F2E';
        an_dump(hexdec($str1));
        an_dump(hexdec('A'), hexdec('B'), hexdec('2'), hexdec('E'));
    }

    public function new_base()
    {
        require MODULE_PATH.'/probe/core/test/db.php';

        $b = new business();
        $b -> set('type', 'day');
        $b -> set('b_id', 23);

        $table = new table($b);
        $sql   = " SELECT * FROM `%s` WHERE `date` = 20170626 ";
        $list  = $table -> select($sql);

        an_dump($list);
    }

    public function a()
    {
        an_dump(1);
        $this -> b();
        an_dump(4);
    }

    public function b()
    {
        an_dump(2);
        throw new Exception('xxxxxx');
        an_dump(3);
    }

    public function _exception()
    {
        // try {
            $this -> a();
        //} catch (Exception $e) {
            // echo $e -> getMessage();
        //}
    }

    public function mong_test()
    {



        $this->write(array(
                array(
                    'dev'   =>  '12344321',
                    'mac'   =>  '1:2:4:3:5:5',
                    'rssi'  =>  '-1',
                    'time'  =>  time()
                )));
    }
}
?>
