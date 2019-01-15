<?php

/**
 * probe_log表内容展示
 *
 * @author  wangl
 */
class Action
{
    /**
     * log内容页
     *
     * @return  String
     */
    public function index()
    {
        $date   = Request::Get('date', (int)date('Ymd'));
        $list   = _model('probe_log')->getList(array('date' => $date), ' ORDER BY `id` DESC ');

        $table  = '<table border="1" cellpadding="0" cellspacing="0" style="text-align: center; width: 600px;">';
        $th     = '<tr><th>ID</th><th>资源名</th><th>日期</th><th>内容</th><th>添加时间</th></tr>';
        $td     = '';

        foreach ( $list as $k => $v ) {
            $td .= '<tr><td>'.$v['id'].'</td><td>'.$v['res_name'].'</td><td>'.$v['date'].'</td><td>'.$v['content'].'</td><td>'.$v['add_time'].'</td></tr>';
        }

        $table .= $th.$td;
        $table .= '</table>';

        echo $table;
    }

    /**
     * 清空表
     *
     * @return  String
     */
    public function truncate()
    {
        $sql = "TRUNCATE `probe_log`";

        _model('probe_log')->getAll($sql);

        echo 'ok';
    } 
}
