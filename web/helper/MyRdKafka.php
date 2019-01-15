<?php
/**
  * alltosun.com kafka操作类 MyRdKafka.php
  * ============================================================================
  * 版权所有 (C) 2009-2013 北京互动阳光科技有限公司，并保留所有权利。
  * 网站地址: http://www.alltosun.com
  * ----------------------------------------------------------------------------
  * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
  * ============================================================================
  * $Author: 王敬飞 (wangjf@alltosun.com) $
  * $Date: 2018年3月20日 下午5:25:23 $
  * $Id$
  */
class MyRdkafka
{
    private $brokers = "192.168.2.21:9093,192.168.2.22:9093/kafka";

    /**
     * 获取生产者实例
     */
    public function get_produce()
    {
        //kafka配置 消费者和生产者提供配置。
        $conf = new RdKafka\Conf();

        //设置错误回调 错误回调被librdkafka用来向应用程序发回CIRCAL错误
        $conf->setErrorCb(function ($kafka, $err, $reason) {

            //rd_kafka_err2str: 将rdkafka错误代码转换为字符串
            //printf("Kafka error: %s (reason: %s)\n", rd_kafka_err2str($err), $reason);
            MyLogger::kafkaLog()->info("Kafka error: ".rd_kafka_err2str($err)." (reason: ".$reason.")\n", array('path' => 'setErrorCb'));
        });

        //主题实例的配置
        $topic_conf = new RdKafka\TopicConf();

        //设置分区程序  将分区器设置为用于根据密钥将消息路由到分区。
        $topic_conf->setPartitioner(RD_KAFKA_MSG_PARTITIONER_CONSISTENT);

        /* 设置投递报告回调
         * 对于RdKafka \ ProducerTopic :: produce（）所接受的每条消息，传递报告回调将被调用一次，并err设置为指示产生请求的结果
         * 当消息成功生成或者librdkafka遇到永久性故障或用于临时错误的重试计数器已用尽时，会调用该回调。
         * 应用程序必须定期调用RdKafka::poll() 以提供排队的传递报告回调。
         */
        $conf->setDrMsgCb (function(RdKafka\Producer $kafka, RdKafka\Message $message) {
            if ($message->err) {
                //rd_kafka_err2str: 将rdkafka错误代码转换为字符串
                MyLogger::kafkaLog()->info(rd_kafka_err2str($message->err), array('path' => 'setDrMsgCb'));
            } else {

            }
        });

        //设置默认主题配置 设置用于自动订阅主题的默认主题配置。这可以与RdKafka \ KafkaConsumer :: subscribe（）或RdKafka \ KafkaConsumer :: assign（）一起使用。
        $conf->setDefaultTopicConf($topic_conf);

        //生产实例
        $rk = new RdKafka\Producer($conf);
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers($this->brokers);
        return $rk;
    }

    /**
     * 生成并发送一条消息
     * @param unknown $topic_name
     * @param unknown $content
     */
    public function produce($topic_name, $content)
    {
        if (is_array($content)) {
            $content = json_encode($content);
        }

        $content .= "<br>";

        $rk = $this->get_produce();
        $topic = $rk->newTopic($topic_name);

        /*
         * produce
         * 第一个参数是分区。RD_KAFKA_PARTITION_UA表示 未分配，并让librdkafka选择分区（自动分区）
         * 第二个参数是消息标志，并且应该始终为0。
         */
        $topic->produce(RD_KAFKA_PARTITION_UA, 0, $content);

        /*
         * poll
         * 参数：指定呼叫将阻止等待事件的最长时间（以毫秒为单位）。对于非阻塞呼叫，请提供0 timeout_ms。要无限期地等待事件，请提供-1
         */
        $rk->poll(0);

        return true;
    }

    /**
     * 获取消费者实例
     */
    public function get_consumer($topic_name)
    {
        $conf = new RdKafka\Conf();

        //设置重新平衡回调以用于协调的消费者组平衡。
        $conf->setRebalanceCb(function (RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
            switch ($err) {
                //更新librdkafka的赋值集  partitions参数是RdKafka \ TopicPartition的数组，表示已分配或已撤销的完整分区集。
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    //                     echo "Assign: ";
                    //                     var_dump($partitions);
                    $kafka->assign($partitions);
                    break;

                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    //                     echo "Revoke: ";
                    //                     var_dump($partitions);
                    $kafka->assign(NULL);
                    break;

                default:
                    throw new \Exception($err);
            }
        });

        $conf->set('group.id', 'myConsumerGroup');

        $conf->set('metadata.broker.list', $this->brokers);

        $topicConf = new RdKafka\TopicConf();

        $topicConf->set('auto.offset.reset', 'smallest');

        $conf->setDefaultTopicConf($topicConf);

        $consumer = new RdKafka\KafkaConsumer($conf);

        $consumer->subscribe($topic_name);

        return $consumer;
    }

    public function consume($topic_name, $callback_param)
    {
        if (empty($callback_param['module']) || empty($callback_param['func'])){
            return '参数不完整';
        }

        $consumer = $this->get_consumer($topic_name);

        while (true) {
            //消费消息并触发回调  参数：超时时间
            $message = $consumer->consume(60);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    _widget($callback_param['module'])->$callback_param['func']($message->payload);
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    echo "没有更多消息<br>";
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    echo "超时<br>";
                    break;
                default:
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }

        }

    }

}