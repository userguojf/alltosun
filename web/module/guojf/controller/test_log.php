<?php

class Action
{
    function log()
    {
        MyLogger::test();

    }

    function kafka()
    {
        $topicConf = new RdKafka\TopicConf();
        $topicConf->setPartitioner(RD_KAFKA_MSG_PARTITIONER_CONSISTENT);

        $conf = new RdKafka\Conf();

        $conf->setErrorCb(function ($kafka, $err, $reason) {
            printf("Kafka error: %s (reason: %s)\n", rd_kafka_err2str($err), $reason);
        });

        $conf->setDrMsgCb(function (RdKafka\Producer $kafka, RdKafka\Message $message) {
            if ($message->err) {
                // message permanently failed to be delivered
                echo rd_kafka_err2str($message->err);
                //echo $message->errstr(); //errstr函数只是对consumer
            } else {
                // message successfully delivered
            }
        });

        $conf->setDefaultTopicConf($topicConf);

        $rk = new RdKafka\Producer($conf);
        $rk->setLogLevel(LOG_DEBUG);
        $rk->addBrokers("192.168.2.21:9093,192.168.2.22:9093/kafka");

        $topic = $rk->newTopic("test2-1");

        for ($i = 0; $i < 10; $i++) {
            $topic->produce(RD_KAFKA_PARTITION_UA, 0, "Message $i");
            //$topic->produce(2, 0, "Message $i");
            $rk->poll(0);
        }

        while ($rk->getOutQLen() > 0) {
            $rk->poll(50);
        }
    }

    function kafka2()
    {
        $conf = new RdKafka\Conf();

// Set a rebalance callback to log partition assignments (optional)
        $conf->setRebalanceCb(function (RdKafka\KafkaConsumer $kafka, $err, array $partitions = null) {
            switch ($err) {
                case RD_KAFKA_RESP_ERR__ASSIGN_PARTITIONS:
                    echo "Assign: ";
                    var_dump($partitions);
                    $kafka->assign($partitions);
                    break;

                case RD_KAFKA_RESP_ERR__REVOKE_PARTITIONS:
                    echo "Revoke: ";
                    var_dump($partitions);
                    $kafka->assign(NULL);
                    break;

                default:
                    throw new \Exception($err);
            }
        });

// Configure the group.id. All consumer with the same group.id will consume
// different partitions.
        $conf->set('group.id', 'myConsumerGroup');

// Initial list of Kafka brokers
        $conf->set('metadata.broker.list', "192.168.2.21:9093,192.168.2.22:9093/kafka");

        $topicConf = new RdKafka\TopicConf();

// Set where to start consuming messages when there is no initial offset in
// offset store or the desired offset is out of range.
// 'smallest': start from the beginning
        $topicConf->set('auto.offset.reset', 'smallest');

// Set the configuration to use for subscribed/assigned topics
        $conf->setDefaultTopicConf($topicConf);

        $consumer = new RdKafka\KafkaConsumer($conf);

// Subscribe to topic 'test'
        $consumer->subscribe(['test2-1']);

        echo "Waiting for partition assignment... (make take some time when\n";
        echo "quickly re-joining the group after leaving it.)\n";

        //exit;
        while (true) {
            $message = $consumer->consume(10 * 1000);
            switch ($message->err) {
                case RD_KAFKA_RESP_ERR_NO_ERROR:
                    //var_dump($message);
                    echo $message->partition . " " . $message->payload . "\n";
                    break;
                case RD_KAFKA_RESP_ERR__PARTITION_EOF:
                    echo "No more messages; will wait for more\n";
                    break;
                case RD_KAFKA_RESP_ERR__TIMED_OUT:
                    echo "Timed out\n";
                    break;
                default:
                    throw new \Exception($message->errstr(), $message->err);
                    break;
            }

            //break;
        }

    }

}