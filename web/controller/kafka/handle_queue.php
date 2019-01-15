<?php
/**
  * alltosun.com 处理队列消息 handle_queue.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年4月11日 下午2:38:19 $
  * $Id: handle_queue.php 429426 2018-06-05 03:19:19Z shenxn $
  */

require ROOT_PATH.'/helper/MyRdKafka.php';

//后台执行
ignore_user_abort(true);
set_time_limit(0);
class Action
{
    public function add_content_stat () {
        $kf = new MyRdkafka();
        $consumer = $kf->get_consumer(array('screen-api-3-content-content_stat-add_content_stat'));

        while (1) {
            //消费消息并触发回调  参数：超时时间
            $message = $consumer->consume(60);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    $param = explode('<br>', rtrim($message->payload, '<br>'));
                    foreach ($param as $k => $v) {
                        $arr = json_decode($v, true);
                        _widget('screen_content.kafka')->add_content_stat($arr);
                    }
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    //echo "没有更多消息<br>";
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    //echo "超时<br>";
                    break;
                default:
                    MyLogger::kafkaLog()->error($message->errstr().var_export($message->err, true), array('path' => 'consume_$message'));
                    //throw new \Exception($message->errstr(), $message->err);
                    break;
            }

            //延迟两秒执行
            sleep(2);
        }
    }
}