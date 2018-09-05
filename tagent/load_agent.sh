#!/bin/bash

DIR="$( cd "$( dirname "$0"  )" && pwd  )"
pidfile=$DIR/data/tagent.pid
app=$DIR/bin/tagent

function check_pid() {
    if [ -f $pidfile ];then
    pid=`cat $pidfile`
        if [ -n $pid ];then
            running=`ps -p $pid|grep -v "PID TTY" |wc -l`
            return $running
        fi
    fi
    return 0
}

function start() {
    check_pid
    # 假如某天线上tagent出现问题, 需要手动执行这一句
    export LD_LIBRARY_PATH=/usr/local/tagent/lib:$LD_LIBRARY_PATH
    #running=$?
    #if [ $running -gt 0 ];then
    #    echo -n "$app now is running already, pid="
    #    cat $pidfile
    #    return 1
    #fi
    #nohup $app &
    #sleep 1
    #running=`ps -p $! | grep -v "PID TTY" | wc -l`
    #if [ $running -gt 0 ];then
    #    echo $! > $pidfile
    #    echo "$app started..., pid=$!"
    #else
    #    echo "$app failed to start."
    #    return 1
    #fi  
    
    
    # 使用supervise方式启动
    /home/work/supervise/bin/supervise -p /home/work/supervise/status/tagent -f "/usr/local/tagent/bin/tagent" > /usr/local/tagent/logs/out.log 2>&1
}

function stop() {
    # pid=`cat $pidfile`
    # kill $pid
    # rm -f $pidfile
    # echo "$app stoped... OK"
    ps -ef | grep /usr/local/tagent/bin/tagent | grep -v grep | cut -c 9-15 | xargs kill -s 9 
}

function status() {
    # check_pid
    # running=$?
    # if [ $running -gt 0 ];then
    #     echo started
    # else
    #     echo stoped
    # fi
    ps -ef | grep /usr/local/tagent/bin/tagent | grep -v grep | cut -c 9-15
}

function init() {
    make
    rm -rf nohup.out
    rm -rf data/tagent.data
    echo "done"
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
restart)
    stop
    sleep 1
    start
    ;;
init)
    init
    ;;
*)

echo "Usage: $0 {start|stop|restart|status|build}"
exit 1
esac
