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

class Action_SsdbMutiGet extends Ap_Action_Abstract
{


    public function execute()
    {

        $par = array();
        $par['host'] = "***.***.***.***";   // ssdb ip地址
        $par['port'] = "port";          // ssdb 端口号
        $ssdb = new Tnoah_SsdbUtil($par);

        $arrRequest = Saf_SmartMain::getCgi();
        $arrInput = $arrRequest['get'];
        $rowkey = $arrInput["row_key"];

        $arr = $ssdb->get($rowkey);

        Tnoah_OutPutUtil::response($arr);


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
