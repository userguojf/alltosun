<?php
/**
 * alltosun.com  test.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-12-27 上午11:41:31 $
 * $Id$
 */
class Action
{
    
    public function index()
    {
        $file_path = ROOT_PATH.'/images/data/screen/json.txt';

        if(file_exists($file_path)){

            $str = file_get_contents($file_path);//将整个文件内容读入到一个字符串中

//             $str = str_replace("\r\n","<br />",$str);
            echo $str;
        } else {
            echo '文件不存在';
        }
    }
    
    public function mongo()
    {
        $result       = _mongo('screen', 'screen_click_record')->aggregate(array(
                array(
                        '$match' => array(
                                    'day' => array('$gte' => 20180702, '$lte' => 20180708)
                                    )
                    ),
                array(
                        '$group' => array(
                                '_id'               => array(
                                        'device_unique_id'  => '$device_unique_id',
                                    ),
        
                                'count'  => array('$sum' => '$click_num'),
                            )
                    ),
                array(
                        '$sort' => array('day' => -1)
                    )
        ));
    
        $res = [];

        $list = $result->toArray();
        foreach ($list as $k => $v) {
            $arr = (array)$v['_id']['device_unique_id'];
//             p($arr[0]);
//             p($v['count']);

            $res[$arr[0]] = $v['count'];
        }
        rsort($res);
        p($res);
//         p(rsort($res));
    }
}