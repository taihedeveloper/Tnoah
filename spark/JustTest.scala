package com.taihe.tnoah

import java.nio.ByteBuffer
import java.nio.ByteOrder
import java.util.ArrayList
import java.util.List
import java.util.Map
import java.util.TreeMap
import java.util.concurrent.CountDownLatch
import java.util.concurrent.ExecutorService
import java.util.concurrent.Executors
import util.control.Breaks._

object JustTest {

  class Node {
    private var name: String = ""
    private var count: Int = 0

    def this(i: String) {
      this()
      this.name = i
    }

    def getName: String = {
      return name
    }

    def setName(name: String) {
      this.name = name
    }

    def getCount: Int = {
      return count
    }

    def inc {
      count += 1
    }
  }

  @throws[InterruptedException]
  def main(args: Array[String]) {
    val ndList: ArrayList[Node] = new ArrayList[Node]
    var i: Int = 0
    //    while (true) {
    //      {
    //
    //        if (({
    //          i += 1; i - 1
    //        }) == 3) break() //todo: break is not supported
    //      }
    //    }
    breakable(
      for(i<-0 until 10) {
        ndList.add(new Node("192.168.2."+i.toString))
        if(i==3){
          break()
        }
      }
    )
    val sh: JustTest[Node] = new JustTest[Node](ndList)
    System.out.println(sh.getNode("222nihao22222").getName)
  }
}

class JustTest[T](var realNodes: ArrayList[T]) {

  private val length: Int = 3
  private var virtualNodes: TreeMap[Long, T] = null
  init

  def getReal: ArrayList[T] = {
    return realNodes
  }

  private def init {
    virtualNodes = new TreeMap[Long, T]
    var i: Int = 0
    while (i < realNodes.size) {
      {
        var j: Int = 0
        while (j < length) {
          {
            virtualNodes.put(hash("aa" + i + j), realNodes.get(i))
          }
          ({
            j += 1; j - 1
          })
        }
      }
      ({
        i += 1; i - 1
      })
    }
  }

  @SuppressWarnings(Array("unchecked")) def getNode(key: String): T = {
    val hashedKey: Long = hash(key)
    val en: Map.Entry[_, _] = virtualNodes.ceilingEntry(hashedKey)
    if (en == null) {
      return virtualNodes.firstEntry.getValue.asInstanceOf[T]
    }
    return en.getValue.asInstanceOf[T]
  }

  private def hash(key: String): Long = {
    val buf: ByteBuffer = ByteBuffer.wrap(key.getBytes)
    val seed: Int = 0x1234ABCD
    val byteOrder: ByteOrder = buf.order
    buf.order(ByteOrder.LITTLE_ENDIAN)
    val m: Long = 0xc6a4a7935bd1e995L
    val r: Int = 47
    var h: Long = seed ^ (buf.remaining * m)
    var k: Long = 0L
    while (buf.remaining >= 8) {
      {
        k = buf.getLong
        k *= m
        k ^= k >>> r
        k *= m
        h ^= k
        h *= m
      }
    }
    if (buf.remaining > 0) {
      val finish: ByteBuffer = ByteBuffer.allocate(8).order(ByteOrder.LITTLE_ENDIAN)
      finish.put(buf).rewind
      h ^= finish.getLong
      h *= m
    }
    h ^= h >>> r
    h *= m
    h ^= h >>> r
    buf.order(byteOrder)
    return h
  }
}

