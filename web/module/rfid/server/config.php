<?php


//正式环境
if (!ONDEV) {

    //设置url
    define('RFID_SITE_URL', 'http://mac.pzclub.cn');

    define('HOST', '0.0.0.0');
    define('PORT', '8080');
    define("DB_HOST", 'DB02');
    define("DB_PORT", '3306');
    define("DB_NAME", '201512awifi');
    define("DB_USER", '201512awifi');
    define("DB_PASS", '9A4vDt3cFMq3Wmxc!!@');

    //define("REDIS_HOST", '172.16.0.205');
    define("REDIS_HOST", '192.168.1.122');
    define("REDIS_PORT", '6379');
    define("REDIS_PASS", '');
    //define("REDIS_DBINDEX", 0);

//开发机
} else {

    define('HOST', '0.0.0.0');
    define('PORT', '19090');

    define("DB_HOST", 'localhost');
    define("DB_PORT", '3306');
    define("DB_NAME", '201512awifi');
    define("DB_USER", '201512awifi');
    define("DB_PASS", 'REa4GHFUZUm86vCX');

    define("REDIS_HOST", '127.0.0.1');
    define("REDIS_PORT", '6379');
    define("REDIS_PASS", '');
    //define("REDIS_DBINDEX", 0);

    //设置url
    define('RFID_SITE_URL', 'http://201711awifiprobe.alltosun.net');

}

//数据前缀
define('KEY_SECRET', 'rfid_');
//手机唯一标示前缀
define('PHONE_SECRET', 'phone_');
//请求时间的缓存刷新间隔
define('REFRESH_INTERVAL', 3);
//最大请求间隔的清除时间
define('CLEAR_INTERVAL', 60);
//体验达标时长
define('QUALIFIED_INTERVAL', 3);
//请求探针数据的时间 Redis缓存key
define('REQUEST_PROBE_TIME', 'request_probe_time');