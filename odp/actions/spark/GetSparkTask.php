<?php
/**
 * @name Action_GetSparkTask
 * @desc 获取spark任务
 * @author ydp
 */
 class Action_GetSparkTask extends Ap_Action_Abstract {
     public function execute() {

         $arrRequest = Saf_SmartMain::getCgi();
         $arrInput = $arrRequest['get'];
         $appArgs = $arrInput["appArgs"];
         $pid = $arrInput["pid"];


         //
         $service = Service_LibFactory::getInstance("Service_SparkTask");
         $result = $service->getAllSparkTask();
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