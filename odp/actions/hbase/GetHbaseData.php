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

class Action_GetHbaseData extends Ap_Action_Abstract
{

    function multiple_threads_request($nodes)
    {
        $arr_ret = array();
        $mh = curl_multi_init();
        $curl_array = array();
        foreach ($nodes as $i => $url) {
            $curl_array[$i] = curl_init($url);
            curl_setopt($curl_array[$i], CURLOPT_RETURNTRANSFER, true);
            curl_multi_add_handle($mh, $curl_array[$i]);
        }
        $running = NULL;
        do {
            usleep(1);
            curl_multi_exec($mh, $running);
        } while ($running > 0);

        $res = array();
        foreach ($nodes as $i => $url) {
            $res[] = curl_multi_getcontent($curl_array[$i]);
        }

        foreach ($nodes as $i => $url) {
            curl_multi_remove_handle($mh, $curl_array[$i]);
        }
        curl_multi_close($mh);
        foreach ($res as $key => $vlaue) {
            $str = json_decode($vlaue, true);
            $arr_ret[] = $str;
        }

        return $arr_ret;
    }

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

    public function timeforDay($starttime)
    {
        $time_str = date('Y-m-d H', $starttime);
        $year = ((int)substr($time_str, 0, 4));//取得年份
        $month = ((int)substr($time_str, 5, 2));//取得月份
        $day = ((int)substr($time_str, 8, 2));//取得几号
        $hour = ((int)substr($time_str, 11, 2));//取得小时
        $min = ((int)substr($time_str, -2));//取得min
        $time_str_temp = mktime(0, 0, 0, $month, $day, $year);
        return $time_str_temp;
    }

    public function makeDate($arr, $starttime, $col, $ip, $type_num)
    {
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

        //end
        //在这取2min或者20min等
        $merge_redis_ret = array();
        $temp_num = 0; //计数
        $temp_sum = 0;
        foreach ($arr_ret[$col][$ip] as $time_str => $item) {
            if ($temp_num == intval($type_num)) {
                $temp_sum = $temp_sum / intval($type_num);
                $merge_redis_ret[$time_str] = $temp_sum;
                $temp_num = 0;
                $temp_sum = 0;
            }
            $temp_sum = $temp_sum + $item;
            $temp_num = $temp_num + 1;
        }
        $arr_ret[$col][$ip] = $merge_redis_ret;
        //end
        return $merge_redis_ret;
    }

    //获取今天的所有数据
    public function getDayDate($redis, $ssdb, $starttime, $endtime, $col, $ip,$type_num)
    {
        $end_0_time = $this->timefor($endtime);
        $start_0_time = $this->timeforDay($endtime);
        $start_0_time_pre = $this->timeforDay($endtime);
        $end_time_str = date("Y-m-d-H", $end_0_time);
        $db_arr = array();
        $ssdb_row_keys = array();
        while ($start_0_time <= $end_0_time) {
            $row_key = $col . "_" . $ip . "_" . date("Y-m-d-H", $start_0_time);
            if ($start_0_time === $end_0_time) {
                //$arr = $redis->get($row_key);
                //$arr = unserialize($arr);
                $arr = $redis->zRange($row_key,0,500,true);
                $db_arr[] = $arr;
            } else {
                $arr = $ssdb->get($row_key);
                $str = "http://127.0.0.1:8080/tnoah/ssdbmutiget?row_key=".$row_key;
                $ssdb_row_keys[] = $str;
                if (!empty($arr)) {
                    $db_arr[] = unserialize($arr);
                }
            }
            $start_0_time = $start_0_time + 3600;
        }
        //$muti_ret = $this->multiple_threads_request($ssdb_row_keys);
        //Tnoah_OutPutUtil::response($muti_ret);

        //var_dump($db_arr);die;
        foreach ($db_arr as $k => $col_value) {
            foreach ($col_value as $key => $val) {
                $time_str = substr($key, -10);
                $time_str = date('Y-m-d H:i:s', $time_str);
                $year = ((int)substr($time_str, 0, 4));//取得年份
                $month = ((int)substr($time_str, 5, 2));//取得月份
                $day = ((int)substr($time_str, 8, 2));//取得几号
                $hour = ((int)substr($time_str, 11, 2));//取得小时
                $min = ((int)substr($time_str, -5, 2));//取得min
                $sec = ((int)substr($time_str, -2));//取得sec
                $time_str = mktime($hour, $min, $sec, $month, $day, $year);
                if ($start_0_time_pre > $time_str) {
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
        }
//        Tnoah_OutPutUtil::response($arr_ret);
        //在这求平均值
        foreach ($arr_ret[$col][$ip] as $time_str => $item) {
            $arr_ret[$col][$ip][$time_str] = $item / ($arr_ret_num[$col][$ip][$time_str]);
        }
        //end
//        Tnoah_OutPutUtil::response($arr_ret);
        //在这取2min或者20min等
        if ($type_num) {
            $merge_redis_ret = array();
            $temp_num = 0; //计数
            $temp_sum = 0;
            foreach ($arr_ret[$col][$ip] as $time_str => $item) {
             //   if ($temp_num == intval($type_num)*6) {
             //       $temp_sum = $temp_sum / (intval($type_num)*6);
                   if ($temp_num == intval(50)) {
                $temp_sum = $temp_sum / intval(50);
                    $merge_redis_ret[$time_str] = $temp_sum;
                    $temp_num = 0;
                    $temp_sum = 0;
                }
                $temp_sum = $temp_sum + $item;
                $temp_num = $temp_num + 1;
            }
            $arr_ret[$col][$ip] = $merge_redis_ret;
        }
        //end
//去掉时间不对的数据
        $final_ret = array();
        foreach ($arr_ret as $key => $value) {
            foreach ($value as $m_key => $m_value) {
                foreach ($m_value as $s_key => $s_value) {
                    if ($endtime > $s_key) {
                        $final_ret[$key][$m_key][$s_key] = $s_value;
                    }
                }
            }
        }
//end
    return $final_ret;
    }


    public function execute()
    {
        $par = array();
        $par['host'] = "***.***.***.***";  // SSDB ip
        $par['port'] = "port";         // SSDB port
        $ssdb = new Tnoah_SsdbUtil($par);
        $redis = new Redis();
        $redis->connect('***.***.***.***', port);  // redis 地址、端口

        $arrRequest = Saf_SmartMain::getCgi();
        $arrInput = $arrRequest['get'];
        $ips = $arrInput["ips"];
        $starttime = $arrInput["starttime"];
        $endtime = $arrInput["endtime"];
        $cols = $arrInput["cols"];
        $type = $arrInput["type"];
        $type_num = $arrInput["type_num"];
	// 判断如果是xx:59:59 则给时间加1
	$start_min = date('i', $starttime);
	$start_sec = date('s', $starttime);
	$end_min   = date('i', $endtime);
	$end_sec   = date('s', $endtime);
        if(intval($start_min) == 59 && intval($start_sec) == 59) {
	    ++$starttime;
	}
        if(intval($end_min) == 59 && intval($end_sec) == 59) {
	    ++$endtime;
	}

        $pid = $arrInput["pid"];
        $service = Service_LibFactory::getInstance("Service_SparkTask");
        //  $ret = $service->getSparkTask($pid);
        //  if (empty($ret)) {
        //      $result["error"] = 22001;
        //      Tnoah_OutPutUtil::response($result);
        //  }
        //  $taskid = $ret[0]["taskid"];
        //  $app_name = $ret[0]["app_name"];
        //  $table_name = $ret[0]["table_name"];


        $ip_array = explode(",", $ips);
        $col_array = explode(",", $cols);
//
//        $socket->setSendTimeout(10000000); // 10秒线上可能太长
//        $socket->setRecvTimeout(20000000); //
//        $transport = new TBufferedTransport($socket);
//        $protocol = new TBinaryProtocol($transport);
//        $client = new HbaseClient($protocol);
//
//        $transport->open();

        $attributes = array();// 这个干嘛用的
        $tableName = $table_name;
        $arr_ret = array();
        $arr_ret_num = array();

        if ($type == "sec") {
            foreach ($col_array as $col) {
                foreach ($ip_array as $ip) {
//                    $start_0_time = strtotime(date("Y-m-d-H", $starttime)); //获取到开始时间的0点的时间
//                    $end_0_time = strtotime(date("Y-m-d-H", $endtime)); //获取到结束时间的0点的时间

                    $start_0_time = $this->timefor($starttime);
                    $end_0_time = $this->timefor($endtime);
                    $db_arr = array();
                    while ($start_0_time <= $end_0_time) {
                        $row_key = $col . "_" . $ip . "_" . date("Y-m-d-H", $start_0_time);
                        if ($start_0_time === $end_0_time) {
                              $arr = $redis->zRange($row_key,0,500,true);
                           // $arr = $redis->get($row_key);
                           // $arr = unserialize($arr);
                            $db_arr[] = $arr;
                        } else {
                            $arr = $ssdb->get($row_key);
                            //var_dump($arr);
                            if (!empty($arr)) {
                                $db_arr[] = unserialize($arr);
                            }
                        }
                        $start_0_time = $start_0_time + 3600;
                    }
                    //var_dump($db_arr);
                    foreach ($db_arr as $k => $col_value) {
                        foreach ($col_value as $key => $val) {
                            $time_str = substr($key, -10);
                            $time_str = date('Y-m-d H:i:s', $time_str);
                            $year = ((int)substr($time_str, 0, 4));//取得年份
                            $month = ((int)substr($time_str, 5, 2));//取得月份
                            $day = ((int)substr($time_str, 8, 2));//取得几号
                            $hour = ((int)substr($time_str, 11, 2));//取得小时
                            $min = ((int)substr($time_str, -5, 2));//取得min
                            $sec = ((int)substr($time_str, -2));//取得sec
                            $time_str = mktime($hour, $min, $sec, $month, $day, $year);
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
                    }
                    //  var_dump($arr_ret);
                    //在这求平均值
                    foreach ($arr_ret[$col][$ip] as $time_str => $item) {
                        $arr_ret[$col][$ip][$time_str] = $item / ($arr_ret_num[$col][$ip][$time_str]);
                    }
                    //end
                    //在这取2min或者20min等
                    if ($type == "min") {
                        $merge_redis_ret = array();
                        $temp_num = 0; //计数
                        $temp_sum = 0;
                        foreach ($arr_ret[$col][$ip] as $time_str => $item) {
                            if ($temp_num == intval($type_num)) {
                                $temp_sum = $temp_sum / intval($type_num);
                                $merge_redis_ret[$time_str] = $temp_sum;
                                $temp_num = 0;
                                $temp_sum = 0;
                            }
                            $temp_sum = $temp_sum + $item;
                            $temp_num = $temp_num + 1;
                        }
                        $arr_ret[$col][$ip] = $merge_redis_ret;
                    }
                    //end
                }
            }

            //去掉时间不对的数据
            $final_ret = array();
            foreach ($arr_ret as $key => $value) {
                foreach ($value as $m_key => $m_value) {
                   foreach ($m_value as $s_key => $s_value) {
                        if ($endtime > $s_key) {
                            $final_ret[$key][$m_key][$s_key] = round($s_value,2);
                        }
                    }
                }
            }
            //end

//            $transport->close();
//            $socket->close();
            $redis->close();
            $ssdb->close();

            //----------------------------
            $time_arrr = array();
            $ff_ret = $final_ret;
            $ff = array();
            //先把第一台机器的时间拿出来(应该拿时间最远离查询时间的那一台！！)
            foreach ($ff_ret as $key => $value) {
                foreach ($value as $m_key => $m_value) {
                    ksort($m_value);
                    foreach ($m_value as $s_key => $s_value) {
                        $time_arrr[$m_key] = $s_key;break;
                    }
                    //break;
                }
               // break;
            }
            $max_ip = 0;
            $temp_timenum =0;
            foreach($time_arrr as $ip => $time){
                if(abs($time - $starttime) >= $temp_timenum){
                    $temp_timenum = abs($time - $starttime);
                    $max_ip = $ip;
                }
            }
            //算出最大ip后，把ip对应的时间拿出来
            $time_arrr_max = array();
            foreach ($ff_ret as $key => $value) {
                foreach ($value as $m_key => $m_value) {
                    if($m_key == $max_ip){
                        foreach ($m_value as $s_key => $s_value) {
                            $time_arrr_max[] = $s_key;
                        }
                    }
                   
                }
                
            }
            sort($time_arrr_max);
            $time_count = 0 ;
            $time_sum = count($time_arrr_max);
            //然后给所有机器的数据先排序，否则赋值后可能是乱的
            foreach ($ff_ret as $key => $value) {
                foreach ($value as $m_key => $m_value) {
                    ksort($ff_ret[$key][$m_key]);
                }
            }
            //最后赋值
            foreach ($ff_ret as $key => $value) {
                foreach ($value as $m_key => $m_value) {
                    foreach ($m_value as $s_key => $s_value) {
 if($time_count < $time_sum){
                            $ff[$key][$m_key][$time_arrr_max[$time_count]] = $s_value;
                            $time_count ++ ;
                        }
                        if($time_count === $time_sum){
                            $time_count = 0;
                            break;
                        }if($time_count === count($m_value)){
                            $time_count = 0;
                            break;
                        }
                    }

                }
            }
            Tnoah_OutPutUtil::response($ff);
            //---------------------------- 

          Tnoah_OutPutUtil::response($final_ret);
        }

//        if ($type == "1min") {
//            $db_arr = array();
//            $muti_ret_ret = array();
//            $url_arr = array();
//            foreach ($col_array as $col) {
//                foreach ($ip_array as $ip) {
//                    $start_0_time = $this->timefor($starttime);
//                    $end_0_time = $this->timefor($endtime);
//                    while ($start_0_time <= $end_0_time) {
//                        $row_key = $col . "_" . $ip . "_" . date("Y-m-d-H", $start_0_time);
//                        if ($start_0_time === $end_0_time) {
//                            $arr = $redis->get($row_key);
//                            $arr = unserialize($arr);
//                            $arr = $this->makeDate($arr, $starttime, $col, $ip, $type_num);
//                            $db_arr[$col][$ip][$row_key] = $arr;
//                            $start_0_time = $start_0_time + 3600;
//                        } else {
//                            $row_key = $col . "_" . $ip . "_" . date("Y-m-d-H", $start_0_time);
//                            $str = "http://127.0.0.1:8080/tnoah/innerdata?starttime={$starttime}&endtime={$endtime}&ip={$ip}&col={$col}&pid={$pid}&type={$type}&rowkey={$row_key}&type_num={$type_num}";
//                            $url_arr[] = $str;
//                            $row_key_arr[] = $row_key;
//                            $start_0_time = $start_0_time + 3600;
//                        }
//                    }
//                }
//            }
//            //muti_curl
//            $muti_ret = $this->multiple_threads_request($url_arr);
//            //var_dump($muti_ret);die;
//            $muti_ret[] = $db_arr;
//            foreach ($muti_ret as $muti) {
//                foreach ($muti as $muti_key => $muti_value) {
//                    foreach ($muti_value as $m_key => $m_value) {
//                        foreach ($m_value as $mm_key => $mm_value) {
//                            if (empty($muti_ret_ret[$muti_key][$m_key])) {
//                                $muti_ret_ret[$muti_key][$m_key] = $mm_value;
//                            } else {
//                                $muti_ret_ret[$muti_key][$m_key] = $muti_ret_ret[$muti_key][$m_key] + $mm_value;
//                            }
//                        }
//                    }
//                }
//            }
//            //end
//            //去掉时间不对的数据
//            $final_ret = array();
//            foreach ($muti_ret_ret as $key => $value) {
//                foreach ($value as $m_key => $m_value) {
//                    foreach ($m_value as $s_key => $s_value) {
//                        if ($endtime > $s_key) {
//                            $final_ret[$key][$m_key][$s_key] = $s_value;
//                        }
//                    }
//                }
//            }
//            //end
////            $transport->close();
////            $socket->close();
//            $redis->close();
//            $ssdb->close();
//            Tnoah_OutPutUtil::response($final_ret);
//        }
//-------------------------------------------------------------------------------
        if ($type == "min") {
            $db_arr = array();
            $muti_ret_ret = array();
            $url_arr = array();
            foreach ($col_array as $col) {
                foreach ($ip_array as $ip) {
                    $start_0_time = $this->timeforDay($starttime);
                    $end_0_time = $this->timeforDay($endtime);
                    while ($start_0_time <= $end_0_time) {
                        $row_key = $col . "_" . $ip . "_" . date("Y-m-d", $start_0_time);
                        if ($start_0_time === $end_0_time) {
//                            $arr = $redis->get($row_key);
//                            $arr = unserialize($arr);
//                            $arr = $this->makeDate($arr, $starttime, $col, $ip, $type_num);
                            $arr = $this->getDayDate($redis, $ssdb, $starttime, $endtime, $col, $ip,$type_num);
                            $db_arr[$col][$ip][$row_key] = $arr[$col][$ip];
                            $start_0_time = $start_0_time + 86400;
                        } else {
                            $row_key = $col . "_" . $ip . "_" . date("Y-m-d", $start_0_time);
                            $str = "http://127.0.0.1:8080/tnoah/innerdata?starttime={$starttime}&endtime={$endtime}&ip={$ip}&col={$col}&pid={$pid}&type={$type}&rowkey={$row_key}&type_num={$type_num}";
                            $url_arr[] = $str;
                            $row_key_arr[] = $row_key;
                            $start_0_time = $start_0_time + 86400;
                        }
                    }
                }
            }
            //muti_curl
            $muti_ret = $this->multiple_threads_request($url_arr);
      //     Tnoah_OutPutUtil::response($muti_ret);
            $muti_ret[] = $db_arr;
      //      Tnoah_OutPutUtil::response($muti_ret);
            foreach ($muti_ret as $mutiti) {
                foreach ($mutiti as $mutiti_key=>$muti) {  //到了CPU_IDLE这层
                    foreach ($muti as $muti_key => $muti_value) {
                        foreach ($muti_value as $m_key => $m_value) {
                            foreach ($m_value as $mm_key => $mm_value) {//1519633620
                                $muti_ret_ret[$mutiti_key][$muti_key][$mm_key] = $mm_value;
                            }
                        }
                    }
                }
            }
            //end

            //去掉时间不对的数据
            $final_ret = array();
            foreach ($muti_ret_ret as $key => $value) {
                foreach ($value as $m_key => $m_value) {
                    foreach ($m_value as $s_key => $s_value) {
                        if ($endtime > $s_key) {
                            $final_ret[$key][$m_key][$s_key] = round($s_value,2) ;
                        }
                    }
                }
            }
            //end
//            $transport->close();
//            $socket->close();
            $redis->close();
            $ssdb->close();
             //----------------------------
            $time_arrr = array();
            $ff_ret = $final_ret;
            $ff = array();
            //先把第一台机器的时间拿出来(应该拿时间最远离查询时间的那一台！！)
            foreach ($ff_ret as $key => $value) {
                foreach ($value as $m_key => $m_value) {
                    ksort($m_value);
                    foreach ($m_value as $s_key => $s_value) {
                        $time_arrr[$m_key] = $s_key;break;
                    }
                    //break;
                }
                // break;
            }
            $max_ip = 0;
            $temp_timenum =0;
            foreach($time_arrr as $ip => $time){
                if(abs($time - $starttime) >= $temp_timenum){
                    $temp_timenum = abs($time - $starttime);
                    $max_ip = $ip;
                }
            }
            //算出最大ip后，把ip对应的时间拿出来
            $time_arrr_max = array();
            foreach ($ff_ret as $key => $value) {
                foreach ($value as $m_key => $m_value) {
                    if($m_key == $max_ip){
                        foreach ($m_value as $s_key => $s_value) {
                            $time_arrr_max[] = $s_key;
                        }
                    }

                }

            }
            sort($time_arrr_max);
            $time_count = 0 ;
            $time_sum = count($time_arrr_max);
            //然后给所有机器的数据先排序，否则赋值后可能是乱的
            foreach ($ff_ret as $key => $value) {
                foreach ($value as $m_key => $m_value) {
                    ksort($ff_ret[$key][$m_key]);
                }
            }
            //最后赋值
            foreach ($ff_ret as $key => $value) {
                foreach ($value as $m_key => $m_value) {
                    foreach ($m_value as $s_key => $s_value) {
                        if($time_count < $time_sum){
                            $ff[$key][$m_key][$time_arrr_max[$time_count]] = $s_value;
                            $time_count ++ ;
                        }
                        if($time_count === $time_sum){
                            $time_count = 0;
                            break;
                        }if($time_count === count($m_value)){
                            $time_count = 0;
                            break;
                        }
                    }

                }
            }
            Tnoah_OutPutUtil::response($ff);
            //---------------------------- 
            Tnoah_OutPutUtil::response($final_ret);
        }


        echo "----------\r\n";
        die;
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
