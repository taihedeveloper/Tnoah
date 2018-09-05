<?php
/**
 * @name Action_GetHbaseData
 * @desc GetHbaseData
 * @author ydp
 */

ini_set('display_error', E_ALL);
date_default_timezone_set("Asia/Shanghai");
$GLOBALS['THRIFT_ROOT'] = '/home/work/odp/app/tnoah/script/Thrift';

require_once $GLOBALS['THRIFT_ROOT'] . '/Transport/TTransport.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/Transport/TSocket.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/Protocol/TProtocol.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/Protocol/TBinaryProtocol.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/Protocol/TBinaryProtocolAccelerated.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/Transport/TBufferedTransport.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/Type/TMessageType.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/Factory/TStringFuncFactory.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/StringFunc/TStringFunc.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/StringFunc/Core.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/Type/TType.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/Exception/TException.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/Exception/TTransportException.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/Exception/TProtocolException.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/Hbase.php';
require_once $GLOBALS['THRIFT_ROOT'] . '/Types.php';

use Thrift\Protocol\TBinaryProtocol;
use Thrift\Transport\TBufferedTransport;
use Thrift\Transport\TSocket;
use Hbase\HbaseClient;
use Hbase\ColumnDescriptor;
use Hbase\Mutation;
use Hbase\TScan;

class Action_CtDataToRedis extends Ap_Action_Abstract
{
    public function execute()
    {
        $today = strtotime(date("Y-m-d"), time());
        $today = $today - 86400 - 86400*3 ;
        $today_end = $today + 86399  ;
        //$today_end = $today + 30  ;
        $today_str = date("Y-m-d", $today);

        $ips = "***.***.***.***";
        $starttime = $today;
        $endtime = $today_end;
        $cols = "CPU_IDLE";
        //$cols = "CPU_WAIT_IO";

        $type = "min";

        $pid = 7;
        $service = Service_LibFactory::getInstance("Service_SparkTask");
        $ret = $service->getSparkTask($pid);
        if (empty($ret)) {
            var_dump(5);
            $result["error"] = 22001;
            Tnoah_OutPutUtil::response($result);
        }
        $taskid = $ret[0]["taskid"];
        $app_name = $ret[0]["app_name"];
        $table_name = $ret[0]["table_name"];
       // var_dump($table_name);die;


        $ip_array = explode(",", $ips);
        $col_array = explode(",", $cols);

        $socket = new TSocket('***.***.***.***', 'port');

        $socket->setSendTimeout(20000); // 10秒线上可能太长
        $socket->setRecvTimeout(20000); //
        $transport = new TBufferedTransport($socket);
        $protocol = new TBinaryProtocol($transport);
        $client = new HbaseClient($protocol);

        $transport->open();

        $attributes = array();// 这个干嘛用的
        $tableName = $table_name;
        $arr_ret = array();
        $arr_ret_num = array();

        if ($type == "min") {
            foreach ($col_array as $col) {
                foreach ($ip_array as $ip) {
                    $scan = new TScan();
                    $startRow = $col . "_" . $ip . $starttime;
                    $stopRow = $col . "_" . $ip . $endtime;
                    $scan->startRow = $startRow;
                    $scan->stopRow = $stopRow;
                    $scanner = $client->scannerOpenWithScan($tableName, $scan);
                    for ($i = 0; $i < 20; $i++) {
                        $get_arr = $client->scannerGetList($scanner, 5000);
                        if (!$get_arr) break;
                        foreach ($get_arr as $rowresult) {
                            foreach ($rowresult as $k => $item) {
                                $row = $rowresult->{'row'};
                                $time_str = substr($row, -10);
                                $time_str = date('Y-m-d H:i', $time_str);
                                $year = ((int)substr($time_str, 0, 4));//取得年份
                                $month = ((int)substr($time_str, 5, 2));//取得月份
                                $day = ((int)substr($time_str, 8, 2));//取得几号
                                $hour = ((int)substr($time_str, 11, 2));//取得小时
                                $min = ((int)substr($time_str, -2));//取得min
                                $time_str = mktime($hour, $min, 0, $month, $day, $year);

                                $col_value = $rowresult->columns["info:value"]->value;
                                if (empty($arr_ret[$col][$ip][$time_str])) {
                                    $arr_ret[$col][$ip][$time_str] = $col_value;
                                    $arr_ret_num[$col][$ip][$time_str] = 1;
                                } else {
                                    $arr_ret[$col][$ip][$time_str] = $arr_ret[$col][$ip][$time_str] + $col_value;
                                    $arr_ret_num[$col][$ip][$time_str] = $arr_ret_num[$col][$ip][$time_str] + 1;
                                }

                            }
                        }
                    }
                    //在这求平均值
                    foreach ($arr_ret[$col][$ip] as $time_str => $item) {
                        $arr_ret[$col][$ip][$time_str] = $item / ($arr_ret_num[$col][$ip][$time_str]);
                    }
                    //end
                }
            }
            $transport->close();
            $socket->close();
            //into redis
            $redis = new Redis();
            $connect = $redis->connect('***.***.***.***', "port", 100);
            $arr_ret_str = serialize($arr_ret);
            $redis_key = $cols . "_" . $ips . "_" . $today_str;

            $re_ret = $redis->setex($redis_key, 86400 * 31, $arr_ret_str);
            //如果错了写日志
            //end
            Tnoah_OutPutUtil::response($arr_ret);
        }
    }
}