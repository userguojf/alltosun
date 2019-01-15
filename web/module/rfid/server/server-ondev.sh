#!/bin/sh

### BEGIN INIT INFO
# Provides:          $SERVER_NAME 
# Required-Start:    $remote_fs $network
# Required-Stop:     $remote_fs $network
# Default-Start:     2 3 4 5
# Default-Stop:      0 1 6
# Short-Description: starts $SERVER_NAME
# Description:       starts the PHP FastCGI Process Manager daemon
### END INIT INFO

# Author:Shenxn 
# Date:2017-05-12
# chkconfig --add $SERVER_NAME

currdir=$(cd `dirname $0`; pwd)

PHP_BIN=php
SERVER_PATH=$currdir

SERVER_NAME=rfid-server

#获取到管理进程的id
function getMasterPid()
{
    PID=`/bin/ps axu|grep $SERVER_NAME|grep master|awk '{print $2}'`
    echo $PID
}

#获取到主进程id
function getManagerPid()
{
    MID=`/bin/ps axu|grep $SERVER_NAME|grep manager|awk '{print $2}'`
    echo $MID
}


start(){
    PID=`getMasterPid`
    if [ -n "$PID" ]; then
        echo "$SERVER_NAME is running"
        exit 1
    fi

    echo -n "Starting $SERVER_NAME..."
    sudo -u www $PHP_BIN $SERVER_PATH/server.php --ondev default
    echo -e "\033[33;5m [ done ] \033[0m"
}

stop(){

    PID=`getMasterPid`
    if [ -z "$PID" ]; then
        echo  "$SERVER_NAME is not running"
    else
        echo -n "Gracefully shutting down mail $SERVER_NAME... "
        kill $PID
        echo -e "\033[33;5m [ done ] \033[0m"
    fi

}

status(){
    PID=`getMasterPid`
    if [ -n "$PID" ]; then
        echo "$SERVER_NAME is running"
    else
        echo "$SERVER_NAME is not running"
    fi
}

reload(){
    MID=`getManagerPid`
    if [ -z "$MID" ]; then
        echo "$SERVER_NAME is not running"
        exit 1
    fi

    echo -n "Reload service $SERVER_NAME... "
    kill -USR1 $MID
    echo -e "\033[33;5m [ done ] \033[0m"
}

reloadTask(){
    MID=`getManagerPid`
    if [ -z "$MID" ]; then
        echo "$SERVER_NAME is not running"
        exit 1
    fi
    echo -n "Reload service $SERVER_NAME..."
    kill -USR2 $MID
    echo -e "\033[33;5m [ done ] \033[0m"
}

case "$1" in
    start)
        start
    ;;

    stop)
        stop
    ;;

    status)
        status
    ;;

    force-quit)
        stop
    ;;

    restart)
        stop
        sleep 1
        start
    ;;
    reload)
        reload
    ;;

    reloadtask)
        reloadTask
    ;;
    *)
        echo "Usage: $0 {start|stop|force-quit|restart|reload|reloadtask|status}"
        exit 1
    ;;
esac
