<?php

/**
 * alltosun.com 守护进程类 daemon.php
 * ============================================================================
 * 版权所有 (C) 2009-2011 北京互动阳光科技有限公司，并保留所有权利。
 * 网站地址: http://www.alltosun.com
 * ----------------------------------------------------------------------------
 * 许可声明：这是一个开源程序，未经许可不得将本软件的整体或任何部分用于商业用途及再发布。
 * ============================================================================
 * $Author: 冯卫雪 (fengwx@alltosun.com) $
 * $Date:  2013-2-17 下午12:02:07 $
 * $Id: daemon.php 680 2013-04-19 10:58:29Z anr $
 *
 *
 * 环境:
 * unix 或 linux操作系统
 * PHP 5
 * PHP开启:
 * --sigchild
 * --pcntl
 *
 */

// Log message levels
define('DLOG_TO_CONSOLE', 1);
define('DLOG_NOTICE', 2);
define('DLOG_WARNING', 4);
define('DLOG_ERROR', 8);
define('DLOG_CRITICAL', 16);

class AnDaemon
{

    /**
     * User ID
     *
     * @var int
     */
    public $userID = 48;

    /**
     * Group ID
     *
     * @var integer
     */
    public $groupID = 48;

    /**
     * Terminate daemon when set identity failure ?
     *
     * @var bool
     */
    public $requireSetIdentity = false;

    /**
     * Path to PID file
     *
     * @var string
     */
    protected $pidFile = '';

    /**
     * Home path
     *
     * @var string
     */
    protected $homePath = '/';

    /**
     * Current process ID
     *
     * @var int
     */
    protected $processId = 0;

    /**
     * Is this process a children
     *
     * @var boolean
     */
    protected $isChildren = false;

    /**
     * Is daemon running
     *
     * @var boolean
     */
    protected $isRunning = false;

    protected $logFile = null ;

    /**
     * Constructor
     *
     * @access public
     * @return void
     */
    public function __construct($identity = NULL)
    {
        set_time_limit(0);
        ob_implicit_flush();

        // Init Log file
        if (defined('DAEMON_PATH')) {
            $logPath = DAEMON_PATH;
        }

        if (!is_dir($logPath)) {
            mkdir($logPath, 0754, true);
            @chmod($logPath, 0754);
        }

        $this->logFile = $logPath . $identity . '.php';
        $this->pidFile = $logPath . $identity . '.pid';

        $fp = fopen($this->logFile, 'a');
        fclose($fp);

        @chmod($this->logFile, 0754);

        register_shutdown_function(array(&$this, 'releaseDaemon'));
    }

    /**
     * 开启守护进程
     *
     * @access public
     * @return bool
     */
    public function start()
    {
        $this->logMessage('Starting daemon');

        if (!$this->daemonize()) {
            $this->logMessage('Could not start daemon', DLOG_ERROR);
            return false;
        }

        $this->logMessage('[' . $this->processId . '] Running...');
        $this->isRunning = true;

        return true;
    }

    /**
     * 运行脚本
     */
    public function run($object = null, $method = null, $params = array())
    {
    	if (!$object && !is_object($object)) {
			$this->logMessage("{$object} is not a object");
			exit();
    	}
    	if (!$method) {
			$this->logMessage("{$method} is not a method");
			exit();
    	}
    	if (!method_exists($object, $method)) {
    		$this->logMessage("{$method} is not exist");
    		exit();
    	}
        while ($this->isRunning) {
        	try {
        		error_reporting(0);
	            call_user_func_array(array($object, $method), $params);
        	} catch (Exception $e) {
        		echo $e->getMessage();
        		exit();
        	}
        }
    }

    /**
     * 停止守护进程
     *
     * @access public
     * @return void
     */
    public function stop()
    {
        $this->logMessage('[' . $this->processId . '] Stoping daemon');
        $this->isRunning = false;
    }

    /**
     * 返回守护进程运行状态
     * @return bool
     */
    public function checkRunning() {
        return $this->isRunning;
    }

    /**
     * Signals handler
     *
     * @return void
     */
    public function sigHandler($sigNo)
    {
        switch ($sigNo) {
            case SIGTERM:   // Shutdown
                $this->logMessage('[' . $this->processId . '] Shutdown signal');
                exit();
                break;

            case SIGCHLD:   // Halt
                $this->logMessage('[' . $this->processId . '] Halt signal');
                while (pcntl_waitpid(-1, $status, WNOHANG) > 0);
                break;
        }
    }

    /**
     * Releases daemon pid file
     * This method is called on exit (destructor like)
     *
     * @return void
     */
    public function releaseDaemon()
    {
        if ($this->isChildren && file_exists($this->pidFile)) {
            $this->logMessage('[' . $this->processId . '] Releasing daemon');
            unlink($this->pidFile);
        }
    }

    /**
     * Do task
     *
     * @access protected
     * @return void
     */
    protected function doTask()
    {
        // override this method
    }

    /**
     * Logs message
     *
     * @access protected
     * @return void
     */
    protected function logMessage($msg, $level = DLOG_NOTICE)
    {
        if ($level & DLOG_TO_CONSOLE) {
            print $msg."\n";
        }
        $fp = fopen($this->logFile, 'a+');
        fwrite($fp, date("Y/m/d H:i:s") . "\t" . $msg . "\n");
        fclose($fp);
        return;
    }

    /**
     * Daemonize
     *
     * Several rules or characteristics that most daemons possess:
     * 1) Check is daemon already running
     * 2) Fork child process
     * 3) Sets identity
     * 4) Make current process a session laeder
     * 5) Write process ID to file
     * 6) Change home path
     * 7) umask(0)
     *
     * @return void
     */
    private function daemonize()
    {
        if ($this->isDaemonRunning()) {
            // Deamon is already running. Exiting
            return false;
        }

        if (!$this->forkProcess()) {
            // Coudn't fork. Exiting.
            return false;
        }

        if (!$this->setIdentity() && $this->requireSetIdentity) {
            // Required identity set failed. Exiting
            return false;
        }

        if (!posix_setsid()) {
            $this->logMessage('[' . $this->processId . '] Could not make the current process a session leader', DLOG_ERROR);
            return false;
        }

        if (!$fp = @fopen($this->pidFile, 'w')) {
            $this->logMessage('[' . $this->processId . '] Could not write to PID file' . "[$this->pidFile]", DLOG_ERROR);
            return false;
        } else {
            fputs($fp, $this->processId);
            fclose($fp);

            @chmod($this->pidFile, 0754);
        }

        @chdir($this->homePath);
        umask(0);

        declare(ticks = 1);

        pcntl_signal(SIGCHLD, array(&$this, 'sigHandler'));
        pcntl_signal(SIGTERM, array(&$this, 'sigHandler'));

        return true;
    }

    /**
     * Cheks is daemon already running
     *
     * @access private
     * @return bool
     */
    private function isDaemonRunning()
    {
        $oldPid = @file_get_contents($this->pidFile);
        if ($oldPid !== false && posix_kill(trim($oldPid),0)) {
            $this->logMessage('Daemon already running with PID: '.$oldPid, (DLOG_TO_CONSOLE | DLOG_ERROR));
            return true;
        } else {
            return false;
        }
    }

    /**
     * Forks process
     *
     * @return bool
     */
    private function forkProcess()
    {
        $this->logMessage('Forking...');

        $processId = pcntl_fork();

        // error
        if ($processId == -1) {
            $this->logMessage('Could not fork', DLOG_ERROR);
            return false;
        }
        // parent
        elseif ($processId) {
            $this->logMessage('Killing parent ID:' . $processId);
            exit();
        }
        // children
        else {
            $this->isChildren = true;
            $this->processId = posix_getpid();
            return true;
        }
    }

    /**
     * Sets identity of a daemon and returns result
     *
     * @return bool
     */
    private function setIdentity()
    {
        if (!posix_setgid($this->groupID) || !posix_setuid($this->userID)) {
            $this->logMessage('Could not set identity', DLOG_WARNING);
            return false;
        } else {
            return true;
        }
    }

}

/*********
 * daemon example
*********/

// 加载AnPHP框架
/*
define('DAEMON_PATH', '/www/web/test/daemon/');
define('LOG_TXT', '/www/web/test/daemon/daemon.log.txt');

require_once 'Daemon.php';

try {
    $sendDaemon = new AnDaemon('send');
    $sendDaemon->userID = posix_getuid();
    $sendDaemon->groupID = posix_getgid();

    if ($sendDaemon->start()) {
        while ($sendDaemon->checkRunning()) {
            // do task
            // 调用方法
            // 运行任务脚本
            // 执行$sendDaemon->stop();退出


            usleep(1000000);
        }
        $sendDaemon->stop();
        unset($sendDaemon);
    } else {
        writeLog("Send daemon lock\n");
    }
} catch (Exception $e) {
    writeLog('fail:' . $e->getMessage());
}

function writeLog($info = '')
{
    $handle = fopen(LOG_TXT, 'a+');
    @chmod(LOG_TXT, 0754);
    fwrite($handle, date('Y-m-d H:i:s')."\n".$info);
    fclose($handle);
}
/*
// daemon end
?>