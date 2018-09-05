package com.taihe.tnoah


import java.io.{BufferedReader, DataOutputStream, InputStreamReader}
import java.lang.Double
import java.net.{HttpURLConnection, URL}
import java.text.SimpleDateFormat
import java.util.{ArrayList, List}
import java.util.Date

import JustTest.Node
import net.sf.json.JSONArray
import org.slf4j.LoggerFactory
//import com.alibaba.fastjson.JSON
import scala.collection.mutable.ListBuffer

object Utils {
//  val utillogger = LoggerFactory.getLogger(Utils.getClass)
  def getAvg(list: List[Double]): Double = {
    var sum: Double = 0.0
    var avg: Double = 0.0
    var i: Int = 0
    while (i < list.size) {
      {
        sum += Double.valueOf(list.get(i).toString)
      }
      ({
        i += 1; i - 1
      })
    }
    avg = sum / list.size()
    return avg
  }

  def httpFun(list: ArrayList[LogRetBean],judgeip:String): Int = {
    if(list.size() > 0) {
      val ndList: ArrayList[Node] = new ArrayList[Node]
      ndList.add(new Node(judgeip))
      ndList.add(new Node(judgeip))

      val sh: JustTest[Node] = new JustTest[Node](ndList)
      val hash_ret = sh.getNode("api").getName.toString
      val urlPath: String = new String("http://"+hash_ret+":6081/sendJudgeData")
      //String urlPath = new String("http://localhost:8080/Test1/HelloWorld?name=丁丁".getBytes("UTF-8"));
      // String param= URLEncoder.encode("data={\"items\": [{\"endpoint\": \"lava-test1\",\"metric\": \"response_$9\",\"value\":11113312111,\"judgeType\": \"gauge\",\"timestamp\":154468991}]}","UTF-8");
      //val lrb: LogRetBean = new LogRetBean("lava-test1", "response_$9", 3, "gauge", 157468991)
      //val lrb2: LogRetBean = new LogRetBean("lava-test1", "response_$9", 3, "gauge", 158468991)
      //    val list: ListBuffer[LogRetBean] = ListBuffer()
      //   //val list: util.List[LogRetBean] = new util.ArrayList[LogRetBean]
      //    list.+=(lrb)
      //list.+=(lrb2)


      //jsonObject = JSONSerializer.toJSON(String);
      //javaBean = JSONSerializer.toJava(json);
      //UserBean userBean =  JSONObject.toBean(jsonObject, UserBean.class);
      //JSONObject jsonObject = JSONObject.fromObject(lrb);
      //
      val jsonArr: JSONArray = JSONArray.fromObject(list)
      var str: String = jsonArr.toString

      str = "data={\"items\": " + str + "}"
      //System.out.println(str)
      //        System.exit(0);
      //建立连接
      val url: URL = new URL(urlPath)
      val httpConn: HttpURLConnection = url.openConnection.asInstanceOf[HttpURLConnection]
      //设置参数
      httpConn.setDoOutput(true)
      httpConn.setDoInput(true)
      httpConn.setUseCaches(false)
      httpConn.setRequestMethod("POST")
      //设置请求属性
      httpConn.setRequestProperty("Content-Type", "application/x-www-form-urlencoded")
      httpConn.setRequestProperty("Connection", "Keep-Alive")
      httpConn.setRequestProperty("Charset", "UTF-8")
      //连接,也可以不用明文connect，使用下面的httpConn.getOutputStream()会自动connect
      httpConn.connect
      //建立输入流，向指向的URL传入参数
      val dos: DataOutputStream = new DataOutputStream(httpConn.getOutputStream)
      dos.writeBytes(str)
      dos.flush
      dos.close
      //获得响应状态
      val resultCode: Int = httpConn.getResponseCode
      if (HttpURLConnection.HTTP_OK == resultCode) {
        val sb: StringBuffer = new StringBuffer
        var readLine: String = new String
        val responseReader: BufferedReader = new BufferedReader(new InputStreamReader(httpConn.getInputStream, "UTF-8"))
        //      while ((readLine = responseReader.readLine) != null) {
        //        {
        ////          sb.append(readLine).append("\n")
        //        }
        //      }
        responseReader.close
        //System.out.println(sb.toString)
      }
      httpConn
//      utillogger.error(str)
//      utillogger.error("resultCode:"+resultCode)
      return 0
    } else {
//      utillogger.error("post data is 0")
      return -1
    }


  }

  def isNumeric3(str: String): Boolean = {
    val number: String = "0123456789."
    var i: Int = 0
    while (i < number.length) {
      {
        if (number.indexOf(str.charAt(i)) == -1) {
          return false
        }
      }
      ({
        i += 1; i - 1
      })
    }
    return true
  }


  def stampToDate(s:String): String = {
    val simpleDateFormat = new SimpleDateFormat("yyyy-MM-dd-HH")
    val ss = s + "000"
    val res = simpleDateFormat.format(new Date(ss.toLong))
    return res

  }

  def getDataMin(s:String):String = {
    val simpleDateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm")
    val ss = s + "000"
    val res = simpleDateFormat.format(new Date(ss.toLong))
    val host_str_int = res.indexOf(":");
    val host_str = res.substring(host_str_int + 1, host_str_int + 3);
    return host_str
  }


  def getDataHour(s:String):String = {
    val simpleDateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm")
    val ss = s + "000"
    val res = simpleDateFormat.format(new Date(ss.toLong))
    val host_str_int = res.indexOf(" ");
    val host_str = res.substring(host_str_int + 1, host_str_int + 3);
    return host_str
  }

  def getDataSec(s:String):String = {
    val simpleDateFormat = new SimpleDateFormat("yyyy-MM-dd HH:mm|ss")
    val ss = s + "000"
    val res = simpleDateFormat.format(new Date(ss.toLong))
    val host_str_int = res.indexOf("|");
    val host_str = res.substring(host_str_int + 1, host_str_int + 3);
    return host_str
  }

  def getDataDay(s:String):String = {
    val simpleDateFormat = new SimpleDateFormat("yyyy-MM-dd")
    val ss = s + "000"
    val res = simpleDateFormat.format(new Date(ss.toLong))
    return res.toString
  }

  def getNow():String = {
    val ss = new Date().getTime().toString().substring(0,10)
    return ss
  }

  //  def addList(lrb: LogRetBean): ArrayList[Double] = {
  //
  //    return true
  //  }

}

