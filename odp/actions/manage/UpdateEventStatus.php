<?php
/**
*   更新指定集群报警状态
*   @author dupeng@taihe.com
*/
class Action_UpdateEventStatus extends Ap_Action_Abstract {

    public function execute() {
        $arrRequest      = Saf_SmartMain::getCgi();
        $param           = $arrRequest['request_param'];

        $show_id              = $param['show_id'];
        $show_status       = $param['show_status'];

        $start_time       = $param['start_time'];
        $end_time       = $param['end_time'];
        $shield_type       = $param['shield_type'];
        
        if(!isset($show_id) || !isset($show_status)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }
        if(!isset($start_time) || $start_time == "" || $start_time == 0) {
              $start_time = time();                     
        }
        if(!isset($end_time) || $end_time == "" || $end_time == 0) {
              $end_time = time() + 3600;                  
        }
        if(!isset($shield_type) || $shield_type == "") {
              $shield_type = 0;                
        }

    
        $showObj  = Service_LibFactory::getInstance('Service_Show');
        $infos = $showObj->updateShow($show_id,$show_status,$start_time,$end_time,$shield_type);
        if(NULL === $infos) {
            Tnoah_OutPutUtil::responseSqlError();                        
        }
        $ret = array(
            'error_code' => 22000,
            'data'       => $infos,
        );
        Tnoah_OutPutUtil::response($ret);   
    }
}

             