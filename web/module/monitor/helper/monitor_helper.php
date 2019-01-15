<?php
/**
  * alltosun.com 监控帮助文件 monitor_helper.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2016-7-6 下午5:02:50 $
  * $Id$
  */
class monitor_helper
{
    /**
     * 邮箱报警
     *
     */
    public static function report_by_email($title = '', $content = '', $email = '')
    {
        if (!$title || !$content) {
            return false;
        }

        if (!$email) {
            return '邮箱地址不能为空';
        }

        //发送邮件
        return _widget('email')->send_email($email, $title, $content);
    }

    /**
     * 短信报警
     */    public static function report_by_message($param1, $param2, $phone='')    {
        if (!$param1 || !$param2) {
            monitor_helper::report_by_email('短信报警失败', 'param1或者param2参数为空');
            return false;
        }
        if (!$phone) {            $phone = monitor_config::$phone_array;        }

        if (is_array($phone)) {
            foreach ($phone as $v) {                $params['tel']         = $v;                $params['content']     = json_encode(array( 'param1' => $param1, 'param2' => $param2));                $params['template_id'] = 91551274;
                _widget('message')->send_message($params);
            }        } else {
            $params['tel']         = $phone;
            $params['content']     = json_encode(array( 'param1' => $param1, 'param2' => $param2));
            $params['template_id'] = 91551274;
            return _widget('message')->send_message($params);
        }
    }

    /**
     * 拼接发送内容
     * @param string $info 内容
     */
    public static function join_content($info, $type)
    {


        //查询错误参数
        $times = monitor_config::$warn_rule[$type]['times'];
        $error_list = _model('error_logs')->getList(array(1=>1), 'ORDER BY `id` DESC LIMIT '.$times);
        if (!is_array($error_list) || empty($error_list)) {
            return false;
        }
        //拼接信息
        $time = date('Y-m-d H:i:s', time());
        $content = '北京时间&nbsp;'.$time.'<br><br>'.$info.":<br><br><br><br>";

        ob_start();

            foreach($error_list as $k=>$v)
            {

                echo "<h3>".$v['add_time']."<h3>";
                $error_info = json_decode($v['content'], true);
                p($error_info);
                echo '<br>';
                echo '=====================================================================><br>';
            }
        $content.= ob_get_contents();
        ob_clean();
        return $content;
    }

}