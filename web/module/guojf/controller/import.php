<?php
/**
 * alltosun.com  import.php
 * ============================================================================
 * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 郭剑峰 (guojf@alltosun.com) $
 * $Date: 2017-12-13 下午9:35:56 $
 * $Id$
 */
class Action
{
    public function screen_roll_device_stat()
    {
        exit();
//         $page = tools_helper::get('page', 1);

        $source_table = 'screen_roll_device_stat';
        $to_db        = 'screen';
        $to_table     = 'screen_roll_device_stat';

        if (!$source_table || !$to_table || !$to_db) {
            echo '参数不完整';
            exit;
        }

        //查询表字段
        $fields = _model($source_table, 'group_db')->getAll('show full columns from '.$source_table);
        $new_fields = array();
    
        //处理字段为 int string
        foreach ($fields as $k => $v) {
            if ($v['Field'] == 'id') {
                continue;
            }
            if (strpos($v['Type'], 'int(') !== false) {
                $new_fields[][$v['Field']] = 'int';
            } else {
                $new_fields[][$v['Field']] = 'string';
            }
        }

        $list = _model($source_table, 'group_db')->getList(array( 1 => 1));

        if (!$list) {
            echo '处理完毕';
            exit();
        }

        //插入
        foreach ($list as $k => $v) {
            $new_data = array();
            //循环字段
            foreach ($new_fields as $v1) {
                $field = key($v1);
                if ($v1[$field] == 'string') {
                    $new_data[$field] = (string)($v[$field]);
                } else if ($v1[$field] == 'int') {
                    $new_data[$field] = (int)($v[$field]);
                } else {
                    echo '非法类型';
                    p($field);
                    exit();
                }
            }

             _mongo($to_db, $to_table)->insertOne($new_data);
        }

//         ++$page;
//         //$url = SITE_URL."/data/to_mongodb2?source_table={$source_table}&page={$page}&to_table={$to_table}&to_db={$to_db}";
    
//         $url = SITE_URL."/test/h_test/import_to_mongodb_device_online?page={$page}";
    
//         echo '<script>window.location.href="'.$url.'"</script>';
    
    }
}