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

echo 123;exit();
require_once __DIR__.'/aws-autoloader.php';
use Aws\S3\S3Client;   //声明使用Aws命名空间中的S3Client类
class Action
{
    public function __call()
    {
        //天翼云的API服务器
        $endpoint = 'http://liangliang.oos-website-cn.oos-hq-sh.ctyunapi.cn';

        //Access Key 在天翼云门户网站-帐户管理-API密钥管理中获取
        $accessKey = "8d06de1f9948d8020956";

        //Access Secret 在天翼云门户网站-帐户管理-API密钥管理中获取
        $accessSecret = "c03184864673c166de7a912e43d1e4e12df916f9";

        $arg = [
            'endpoint' => $endpoint,  //声明使用指定的endpoint
            'key'      => $accessKey,
            'secret'   => $accessSecret
        ];

        //创建S3 client 对象
        $client = S3Client::factory($arg);

        header('Content-Type:text/plain');
        //列出所有buckets
        $result = $client->listBuckets();
        p($result);
        exit();
        // foreach ($result['Buckets'] as $bucket) {
            // Each Bucket value will contain a Name and CreationDate
            // echo "{$bucket['Name']} - {$bucket['CreationDate']}\n";
        // }

        echo "\n\n";
    }
}