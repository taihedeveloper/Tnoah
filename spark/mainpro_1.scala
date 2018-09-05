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

object mainpro_1 {
  var time_str_list: ArrayList[String] = new ArrayList[String]
  var time_str_max: String = ""
//  val mylogger = LoggerFactory.getLogger(mainpro_1.getClass)
  def main(args: Array[String]): Unit = {
    Logger.getLogger("org").setLevel(Level.WARN)

    if (args.length < 3) {
      System.err.println("Usage: <topics> <appname>")
      System.exit(1)
    }
    val Array(topics, appname, judgeip) = args

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
      //下面把后面的匹配项拼接起来
      //eg:hostname 192.168.0.0 1504689645 200@1 cost@0.8
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
            //             Utils.getNow
            val now_time =Utils.getNow
            val jedis = RedisUtil.getJedis
            val p: Pipeline = jedis.pipelined
            val hour59Map: HashMap[String, Response[Set[Tuple]]] = new HashMap[String, Response[Set[Tuple]]]
            var http_lrb_list: ArrayList[LogRetBean] = new ArrayList[LogRetBean]
            val map: HashMap[String, Int] = new HashMap[String, Int]()
            val cost_map: HashMap[String, ArrayList[Double]] = new HashMap[String, ArrayList[Double]]()
            val list: ArrayList[Double] = new ArrayList[Double]
            val rowKey = x._1
            val temp_data_key_arr: Array[String] = x._1.split("_")
            val data_key = temp_data_key_arr(1) //ip
            val temp = x._2           // metric@v metric@v
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
                val lrb: LogRetBean = new LogRetBean(host_str, entry.getKey, value, "gauge", now_time.toInt)
                http_lrb_list.add(lrb)
                val time2date = Utils.stampToDate(now_time)
                val data_key_tem = data_key + "_" + time2date
                p.zadd(entry.getKey + "_" + data_key_tem, value.toDouble, now_time.toString)
//                mylogger.error("insertredis:"+entry.getKey + "_" + data_key_tem+"|value:"+value+"|time:"+now_time.toString)
                p.expire(entry.getKey + "_" + data_key_tem, 3600 * 2)
              }
            }

            p.sync
            Utils.httpFun(http_lrb_list,judgeip)
//            jedis.close()
            RedisUtil.returnResource(jedis)
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
