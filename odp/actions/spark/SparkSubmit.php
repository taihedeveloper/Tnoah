<?php
/**
 * @name Action_SparkSubmit
 * @desc 提交spark任务
 * @author ydp
 */
 class Action_SparkSubmit extends Ap_Action_Abstract {
     public function execute() {
         //Tnoah_OutPutUtil::response($result);
         $result = array();
         $arrRequest = Saf_SmartMain::getCgi();
         $arrInput = $arrRequest['get'];
         $appArgs = $arrInput["appArgs"];
         $pid = $arrInput["pid"];
         $taskname = $arrInput["taskname"];
         if(empty($taskname)){
             $result["error"] = 22001;
             $result["err_msg"] = "args error";
             Tnoah_OutPutUtil::response($result);
         }
         $arr_args = explode(',',$appArgs);

         //设置spark-api地址
         $url = "http://***.***.***.***:port/v1/submissions/create";
         //设置header
         $header_arr = array("Content-Type:application/json;charset=UTF-8" );
        //设置参数
         $data = array();
         $data["action"] = "CreateSubmissionRequest";
         $data["appArgs"] = $arr_args;
         $data["appResource"] = "file:/home/work/temp/yandepeng/".$taskname.".jar";
         $data["clientSparkVersion"] = "1.6";
         $data["environmentVariables"] = array();
         $data["environmentVariables"]["SPARK_ENV_LOADED"] = "1";
         $data["mainClass"] = "mainpro";
         $data["sparkProperties"] = array();
         $data["sparkProperties"]["spark.jars"] = "file:/home/work/temp/yandepeng/".$taskname.".jar";
         $data["sparkProperties"]["spark.driver.supervise"] = "true";
         $data["sparkProperties"]["spark.app.name"] = $taskname;
//         $data["sparkProperties"]["spark.eventLog.enabled"] = "true";
         $data["sparkProperties"]["spark.submit.deployMode"] = "cluster";
         $data["sparkProperties"]["spark.cores.max"] = 5;
         $data["sparkProperties"]["spark.driver.memory"] = "900M";
         $data["sparkProperties"]["spark.executor.memory"] = "2000M";
         $data["sparkProperties"]["spark.master"] = "spark://***.***.***.***:port,***.***.***.***:port"; // spark地址
         //$data["sparkProperties"]["spark.queue"] = "dstream";
         //end
         $data_json = json_encode($data);
         //提交任务
         $httpObj = new Tnoah_HttpProxy();
         $ret = $httpObj->post($url,$data_json,1000,1,$header_arr);
         $ret = json_decode($ret,true);

         if(!empty($ret["success"])&&$ret["success"] == true){
             $result["error"] = 22000;
             $result["submissionId"] = $ret["submissionId"];
             //把id更新进DB
             $service = Service_LibFactory::getInstance("Service_SparkTask");
             $rr = $service->updateSparkTask($pid, $result["submissionId"],1);
             //end
             Tnoah_OutPutUtil::response($result);
         }else{
             $result["error"] = 22001;
             //var_dump($ret);
             $result["submissionId"] = $ret["submissionId"];
             Tnoah_OutPutUtil::response($result);
         }




     }
 }
