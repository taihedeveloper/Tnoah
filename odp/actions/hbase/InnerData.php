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
require_once $GLOBALS['THRIFT_ROOT'] . '/Transport/TFramedTransport.php';
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
use Thrift\Transport\TFramedTransport;
use Thrift\Transport\TSocket;
use Hbase\HbaseClient;
use Hbase\ColumnDescriptor;
use Hbase\Mutation;
use Hbase\TScan;

class Action_InnerData extends Ap_Action_Abstract
{
    public function timefor($starttime)
    {
        $time_str = date('Y-m-d H', $starttime);
        $year = ((int)substr($time_str, 0, 4));//取得年份
        $month = ((int)substr($time_str, 5, 2));//取得月份
        $day = ((int)substr($time_str, 8, 2));//取得几号
        $hour = ((int)substr($time_str, 11, 2));//取得小时
        $min = ((int)substr($time_str, -2));//取得min
        $time_str_temp = mktime($hour, 0, 0, $month, $day, $year);
        return $time_str_temp;
    }


    public function execute()
    {

        $par = array();
        $par['host'] = "***.***.***.***";   // ssdb ip地址
        $par['port'] = "port";          // ssdb 端口
        $ssdb = new Tnoah_SsdbUtil($par);

        $arrRequest = Saf_SmartMain::getCgi();
        $arrInput = $arrRequest['get'];
        $rowkey = $arrInput["rowkey"];
        $starttime = $arrInput["starttime"];
        $endtime = $arrInput["endtime"];
        $ip = $arrInput["ip"];
        $col = $arrInput["col"];
        $type_num = $arrInput["type_num"];

        $attributes = array();// 这个干嘛用的
        //$tableName = $table_name;
        $arr_ret = array();
        $arr_ret_num = array();


        $arr = $ssdb->get($rowkey);
        $arr = unserialize($arr);
        //var_dump($arr);
        foreach ($arr as $key => $val) {
            $time_str = substr($key, -10);
            $time_str = date('Y-m-d H:i', $time_str);
            $year = ((int)substr($time_str, 0, 4));//取得年份
            $month = ((int)substr($time_str, 5, 2));//取得月份
            $day = ((int)substr($time_str, 8, 2));//取得几号
            $hour = ((int)substr($time_str, 11, 2));//取得小时
            $min = ((int)substr($time_str, -2));//取得min
            $time_str = mktime($hour, $min, 0, $month, $day, $year);
            if ($starttime > $time_str) {
                continue;
            }
//                            if($endtime < $time_str){
//
//                                break;
//                            }

            if (empty($arr_ret[$col][$ip][$time_str])) {
                $arr_ret[$col][$ip][$time_str] = $val;
                $arr_ret_num[$col][$ip][$time_str] = 1;
            } else {
                $arr_ret[$col][$ip][$time_str] = $arr_ret[$col][$ip][$time_str] + $val;
                $arr_ret_num[$col][$ip][$time_str] = $arr_ret_num[$col][$ip][$time_str] + 1;
            }
        }
        //在这求平均值
        foreach ($arr_ret[$col][$ip] as $time_str => $item) {
            $arr_ret[$col][$ip][$time_str] = $item / ($arr_ret_num[$col][$ip][$time_str]);
        }

        //var_dump($arr_ret);

        //end
        //在这取2min或者20min等
        $merge_redis_ret = array();
        $temp_num = 0; //计数
        $temp_sum = 0;
        foreach ($arr_ret[$col][$ip] as $time_str => $item) {
            if ($temp_num == intval(1)) {
                $temp_sum = $temp_sum / intval(1);
            //if ($temp_num == intval($type_num)) {
            //    $temp_sum = $temp_sum / (intval($type_num));
                $merge_redis_ret[$time_str] = $temp_sum;
                $temp_num = 0;
                $temp_sum = 0;
            }
            $temp_sum = $temp_sum + $item;
            $temp_num = $temp_num + 1;
        }
//        $arr_ret[$col][$ip] = $merge_redis_ret;
        //end

        $arr_ret_ret = array();
        $arr_ret_ret[$col][$ip][$rowkey] = $arr_ret[$col][$ip];
//        $transport->close();
//        $socket->close();
        Tnoah_OutPutUtil::response($arr_ret_ret);


        echo "----------\r\n";
        /******
         *
         * //no test
         *
         * public function scannerOpenWithScan($tableName, \Hbase\TScan $scan, $attributes);
         *
         *
         *
         * public function scannerOpen($tableName, $startRow, $columns, $attributes);
         *
         * public function scannerOpenWithStop($tableName, $startRow, $stopRow, $columns, $attributes);
         *
         * public function scannerOpenWithPrefix($tableName, $startAndPrefix, $columns, $attributes);
         *
         * public function scannerOpenTs($tableName, $startRow, $columns, $timestamp, $attributes);
         *
         * public function scannerOpenWithStopTs($tableName, $startRow, $stopRow, $columns, $timestamp, $attributes);
         *
         * public function scannerGet($id);
         *
         * public function scannerGetList($id, $nbRows);
         *
         * public function scannerClose($id);
         */


        echo "----scanner get ------\r\n";

        $startRow = '1';

        $columns = array('column' => 'score',);


//


        $scan = $client->scannerOpen($tableName, $startRow, $columns, $attributes);


//$startAndPrefix = '13686667';

//$scan = $client->scannerOpenWithPrefix($tableName,$startAndPrefix,$columns,$attributes);


//$startRow = '1';

//$stopRow = '2';

//$scan = $client->scannerOpenWithStop($tableName, $startRow, $stopRow, $columns, $attributes);


//$arr = $client->scannerGet($scan);


        $nbRows = 1000;


        $arr = $client->scannerGetList($scan, $nbRows);


        var_dump('count of result :' . count($arr));


        foreach ($arr as $k => $TRowResult) {

            // code...

            //var_dump($TRowResult);

        }


        $client->scannerClose($scan);


//close transport

        $transport->close();


    }
}
