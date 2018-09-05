<?php
/**
 * @name Action_AddSparkTask
 * @desc 获取spark任务
 * @author ydp
 */
 class Action_AddSparkTask extends Ap_Action_Abstract {
     public function execute() {

         $arrRequest = Saf_SmartMain::getCgi();
         $arrInput = $arrRequest['get'];
         $appArgs = $arrInput["appArgs"];
         $pid = $arrInput["pid"];
         $pname = $arrInput["pname"];


         //
         $service = Service_LibFactory::getInstance("Service_SparkTask");
         $result = $service->insertSparkTask($pid, $pname,"");
         //end
         $ret = array();
         $ret['error'] = 22001;
         if(!empty($result)){
             $ret['error'] = 22000;
             $ret['data'] = $result;
             Tnoah_OutPutUtil::response($ret);
         }
         Tnoah_OutPutUtil::response($ret);






     }
 }