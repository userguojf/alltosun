<?php

define('HOST', '0.0.0.0');
define('PORT', '8081');

define("DB_HOST", 'DB01');
define("DB_PORT", '3306');
define("DB_NAME", '201512awifi');
define("DB_USER", '201512awifi');
define("DB_PASS", '9A4vDt3cFMq3Wmxc');

define("REDIS_HOST", '172.16.0.205');
define("REDIS_PASS", '');


///////// ONDEV ///////

// define('HOST', '0.0.0.0');
// define('PORT', '8080');

// define("DB_HOST", 'localhost');
// define("DB_PORT", '3306');
// define("DB_NAME", '201512awifi');
// define("DB_USER", '201512awifi');
// define("DB_PASS", 'REa4GHFUZUm86vCX');

// define("REDIS_HOST", '127.0.0.1');
// define("REDIS_PASS", '');



///////// ONDEV ///////

//数据前缀
define('KEY_SECRET', 'rfid_');
//手机唯一标示前缀
define('PHONE_SECRET', 'phone_');
//请求时间的缓存刷新间隔
define('REFRESH_INTERVAL', 3);
//最大请求间隔的清除时间
define('CLEAR_INTERVAL', 100);