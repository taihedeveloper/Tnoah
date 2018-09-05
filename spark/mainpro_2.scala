package com.taihe.tnoah

import java.lang.Double
import java.util.{ArrayList, HashMap, Set, TreeMap}

import kafka.serializer.StringDecoder
import org.apache.log4j.{Level, Logger}
import org.apache.spark.SparkConf
import org.apache.spark.streaming.kafka.KafkaUtils
import org.apache.spark.streaming.{Seconds, StreamingContext}
import org.slf4j.LoggerFactory
import redis.clients.jedis.{Jedis, Pipeline, Response, Tuple}

object mainpro_2 {
  var time_str_list: ArrayList[String] = new ArrayList[String]
  var time_str_max: String = ""
//  val mylogger = LoggerFactory.getLogger(mainpro_2.getClass)
  def main(args: Array[String]): Unit = {
    Logger.getLogger("org").setLevel(Level.INFO)

    if (args.length < 2) {
      System.err.println("Usage: <topics> <appname>")
      System.exit(1)
    }
    val Array(topics, appname) = args

    val brokers = "***.***.***.***:port,***.***.***.***:port,***.***.***.***:port"  // kafka配置
    val groupId = topics

    // var conf = new SparkConf().setAppName(appname).setMaster("local")
    var conf = new SparkConf().setAppName(appname)
    var ssc = new StreamingContext(conf, Seconds(10))

    val topicsSet = topics.split(",").toSet
    val kafkaParams = Map[String, String]("metadata.broker.list" -> brokers)

    val messages = KafkaUtils.createDirectStream[String, String, StringDecoder, StringDecoder](ssc, kafkaParams, topicsSet)

    //进行匹配,选出同一类型的日志
    val mapByKey = messages.map(x => {
      val temp = x._2
      val str_arr = temp.split(" ")
      val hostname = str_arr(0).toString
      val ip = str_arr(1).toString
      val timenum = str_arr(2).toString
      if (time_str_list.size() > 5000) {
        time_str_list.clear()
      }
      time_str_list.add(timenum)

      val rowKey = hostname + "_" + ip
      var temp_str = ""
      var i: Int = 3
      while (i < str_arr.length) {
        temp_str = temp_str + str_arr(i) + " "
        i = i + 1
      }

      (rowKey, temp_str)
    })

    //根据ip分组
    val reduceByKey = mapByKey.reduceByKey((x, y) => x + " " + y)
    reduceByKey.foreachRDD(rdd => {
      if (!rdd.isEmpty()) {
        rdd.foreachPartition(fp => {
          fp.foreach(x => {
//            mylogger.error("begin tnoah day data--------------------------------------------------------------")
//            mylogger.error("time_str_list.size:"+time_str_list.size())
            if(time_str_list.size() > 0){
              var time_num: Int = 0
              while (time_num < time_str_list.size()) {
                val temp_str = time_str_list.get(time_num)
                if (time_str_max < temp_str) {
                  time_str_max = temp_str
                }
                time_num = time_num + 1
              }
            }
            val now_time = Utils.getNow
            var jedis: Jedis = new Jedis("***.***.***.***", 13001)  // jedis地址
            val p: Pipeline = jedis.pipelined
            var http_lrb_list: ArrayList[LogRetBean] = new ArrayList[LogRetBean]
            val map: HashMap[String, Int] = new HashMap[String, Int]()
            val cost_map: HashMap[String, ArrayList[Double]] = new HashMap[String, ArrayList[Double]]()
            val list: ArrayList[Double] = new ArrayList[Double]
            val rowKey = x._1
            val temp_data_key_arr: Array[String] = x._1.split("_")
            val data_key = temp_data_key_arr(1)
            val temp = x._2
            val str_arr: Array[String] = temp.split(" ")
            for (strr <- str_arr) {
              val strrr = strr.trim()
              if (strrr != null && strrr != "") {
                val temp_arr: Array[String] = strrr.split("@")
                if (temp_arr.length >= 2) {
                  if (temp_arr(0).trim != "" && temp_arr(1).trim != "") {
                    if (temp_arr(1) != "#") {
                      val tem_val = cost_map.get(temp_arr(0))
                      if (null == tem_val) {
                        val cost_list: ArrayList[Double] = new ArrayList[Double]
                        cost_list.add(Double.valueOf(temp_arr(1)))
                        cost_map.put(temp_arr(0), cost_list)
                      } else {
                        tem_val.add(Double.valueOf(temp_arr(1)))
                        cost_map.put(temp_arr(0), tem_val)
                      }
                    }
                    else {
                      map.put(temp_arr(0), if (map.get(temp_arr(0)) == null) 1
                      else map.get(temp_arr(0)).asInstanceOf[Int] + 1)
                    }
                  }
                }
              }
            }
            import scala.collection.JavaConversions._
            //求平均值的
            for (entry <- cost_map.entrySet) {
              if (Utils.getAvg(entry.getValue).toString != "") {
                val value = Double.valueOf(Utils.getAvg(entry.getValue).toString)
                val host_str_int = x._1.indexOf("_")
                val host_str = x._1.substring(0, host_str_int)
                val time2date = Utils.stampToDate(now_time)
                val data_key_tem = data_key + "_" + time2date

                //每天数据汇总
                var time_hour = Utils.getDataHour(now_time)
                var time_min = Utils.getDataMin(now_time)
                var time_day = Utils.getDataDay(now_time)
                var time_sec = Utils.getDataSec(now_time).toInt
                var key_pre = entry.getKey + "_" + data_key + "_"
                if (time_hour == "23" && time_min == "59") {
                  //                if(true){
                  val new_day_map: HashMap[String, String] = new HashMap[String, String]
                  val ssdb = new Jedis("***.***.***.***", port) // ssdb地址
                  //24个key
                  var ssdb_key0 = key_pre + time_day + "-00"
                  var ssdb_key1 = key_pre + time_day + "-01"
                  var ssdb_key2 = key_pre + time_day + "-02"
                  var ssdb_key3 = key_pre + time_day + "-03"
                  var ssdb_key4 = key_pre + time_day + "-04"
                  var ssdb_key5 = key_pre + time_day + "-05"
                  var ssdb_key6 = key_pre + time_day + "-06"
                  var ssdb_key7 = key_pre + time_day + "-07"
                  var ssdb_key8 = key_pre + time_day + "-08"
                  var ssdb_key9 = key_pre + time_day + "-09"
                  var ssdb_key10 = key_pre + time_day + "-10"
                  var ssdb_key11 = key_pre + time_day + "-11"
                  var ssdb_key12 = key_pre + time_day + "-12"
                  var ssdb_key13 = key_pre + time_day + "-13"
                  var ssdb_key14 = key_pre + time_day + "-14"
                  var ssdb_key15 = key_pre + time_day + "-15"
                  var ssdb_key16 = key_pre + time_day + "-16"
                  var ssdb_key17 = key_pre + time_day + "-17"
                  var ssdb_key18 = key_pre + time_day + "-18"
                  var ssdb_key19 = key_pre + time_day + "-19"
                  var ssdb_key20 = key_pre + time_day + "-20"
                  var ssdb_key21 = key_pre + time_day + "-21"
                  var ssdb_key22 = key_pre + time_day + "-22"
                  var ssdb_key23 = key_pre + time_day + "-23"
                  //24个key end
                  var redis_str_arr = ssdb.mget(ssdb_key0, ssdb_key1, ssdb_key2, ssdb_key3, ssdb_key4, ssdb_key5, ssdb_key6, ssdb_key7, ssdb_key8, ssdb_key9, ssdb_key10, ssdb_key11, ssdb_key12, ssdb_key13, ssdb_key14, ssdb_key15, ssdb_key16, ssdb_key17, ssdb_key18, ssdb_key19, ssdb_key20, ssdb_key21, ssdb_key22, ssdb_key23)
                  //            if(true){
                  var i: Int = 0
//                  mylogger.error("key_pre:"+key_pre)
//                  mylogger.error("time_day:"+time_day)
//                  mylogger.error("redis_str_arr.size:"+redis_str_arr.size())
//                  mylogger.error(redis_str_arr.toString)
                  while (i < redis_str_arr.size()) {
                    {
                      var redis_str = redis_str_arr.get(i)
                      if (null != redis_str && false != redis_str && !redis_str.isEmpty && "null" != redis_str) {
                        //System.out.println(1)
                        val tem_map: HashMap[String, AnyRef] = PHPSerializer.unserialize(redis_str.getBytes).asInstanceOf[HashMap[String, AnyRef]]
                        val tem_tree_map: TreeMap[String, AnyRef] = new TreeMap[String, AnyRef]
                        tem_tree_map.putAll(tem_map)
                        //System.out.println(tem_tree_map)
                        val keys: Set[String] = tem_tree_map.keySet()
                        var num = 0
                        var avg_list: ArrayList[Double] = new ArrayList[Double]
                        for (tem_tree_key <- keys) {
                          num = num + 1
                          var tem_tree_val = tem_tree_map.get(tem_tree_key)
                          avg_list.add(Double.valueOf(tem_tree_val.toString))
                          if (num == 60) {
                            var avg_key = tem_tree_key
                            var avg_num = Utils.getAvg(avg_list)
                            new_day_map.put(avg_key, avg_num.toString)
                            avg_list = new ArrayList[Double]
                            num = 0
                          }
                        }
                      }
                    }
                    ({
                      i += 1;
                      i - 1
                    })
                  }
                  //CPU_IDLE_192.168.2.173_2018-01-11
                  val b: Array[Byte] = PHPSerializer.serialize(new_day_map)
                  val b_str = new String(b)
                  ssdb.set(key_pre + time_day, b_str)
                  ssdb.expire(key_pre + time_day, 86400 * 90)
//                  mylogger.error("ssdb set tnoah day data--------------------------------------------------------------")
//                  mylogger.error(key_pre + time_day+"|"+b_str)
                  ssdb.close()
                }
                //每天数据汇总end
              }
            }
            //            Utils.httpFun(http_lrb_list)
            p.sync

            //处理小时级的数据end
            jedis.close()

          })
          // println(req)
        })
      }
      // System.out.println(http_lrb_list)
    })

    ssc.start()
    ssc.awaitTermination()

  }
}
