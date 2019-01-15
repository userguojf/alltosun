<?php


require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/../config/config_log.php';

require_once __DIR__ . '/AnLogger.php';
require_once __DIR__ . '/MyLogger.php';
AnLogger::init($loggerConfig);

