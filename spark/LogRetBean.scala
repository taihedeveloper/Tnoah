package com.taihe.tnoah


class LogRetBean {
  private var endpoint: String = null
  private var metric: String = null
  private var value: Double = 0
  private var judgeType: String = null
  private var timestamp: Int = 0

  def this(endpoint: String, metric: String, value: Double, judgeType: String, timestamp: Int) {
    this()
    this.endpoint = endpoint
    this.metric = metric
    this.value = value
    this.judgeType = judgeType
    this.timestamp = timestamp
  }

  def getMetric: String = {
    return metric
  }

  def setMetric(metric: String) {
    this.metric = metric
  }

  def getEndpoint: String = {
    return endpoint
  }

  def setEndpoint(endpoint: String) {
    this.endpoint = endpoint
  }

  def getValue: Double = {
    return value
  }

  def setValue(value: Double) {
    this.value = value
  }

  def getJudgeType: String = {
    return judgeType
  }

  def setJudgeType(judgeType: String) {
    this.judgeType = judgeType
  }

  def getTimestamp: Int = {
    return timestamp
  }

  def setTimestamp(timestamp: Int) {
    this.timestamp = timestamp
  }
}

