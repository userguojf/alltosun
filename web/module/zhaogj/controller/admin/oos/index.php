<?php
/**
 * alltosun.com 主页面 index.php
 * ============================================================================
 * 版权所有 (C) 2009-2018 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 赵高举 (zhaogj@alltosun.com) $
 * $Date: 2018/5/28 16:30 $
 * $Id$
 */
require_once __DIR__.'/aws.phar';
use Aws\S3\S3Client;   //声明使用Aws命名空间中的S3Client类
class Action
{
    public function __construct()
    {
        //天翼云的API服务器
        $endpoint = 'http://oos.ctyunapi.cn';

        //Access Key 在天翼云门户网站-帐户管理-API密钥管理中获取
        $accessKey = "0af346016090958b208a";

        //Access Secret 在天翼云门户网站-帐户管理-API密钥管理中获取
        $accessSecret = "c788ebb5853c1cc8df8ece214fa527b4294f014d";

        $arg = [
            'endpoint' => $endpoint,  //声明使用指定的endpoint
            'key'      => $accessKey,
            'secret'   => $accessSecret
        ];

        //创建S3 client 对象
        $client = S3Client::factory($arg);

        //列出所有buckets
        $result = $client->listBuckets(['bucketName' => 'video-test']);
        p($result);
        exit();
        // foreach ($result['Buckets'] as $bucket) {
            // Each Bucket value will contain a Name and CreationDate
            // echo "{$bucket['Name']} - {$bucket['CreationDate']}\n";
        // }

        echo "\n\n";
    }
}