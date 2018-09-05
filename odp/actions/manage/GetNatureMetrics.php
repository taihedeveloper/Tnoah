<?php
/**
*   获取性能类监控项
*   @author zhangweizhi@taihe.com
*/
class Action_GetNatureMetrics extends Ap_Action_Abstract {

    public function execute() {
        $arrRequest    = Saf_SmartMain::getCgi();
        $param         = $arrRequest['request_param'];
        $tag           = $param['tag'];
        if(!isset($tag)) {
            Tnoah_OutPutUtil::responseParamError();
        }
        $validParams = array('CPU', 'MEM', 'DISK', 'DISK_IO', 'NET');
        if(!in_array($tag, $validParams)) {
            Tnoah_OutPutUtil::responseParamError();            
        }
        $metricObj  = Service_LibFactory::getInstance('Service_Metric');
        
        $infos = $metricObj->getNatureInfos($tag);
        if(NULL === $infos || false == $infos) {
            Tnoah_OutPutUtil::responseSqlError();                        
        }
        $ret = array(
            'error_code' => 22000,
            'data'       => $infos,
        );
        Tnoah_OutPutUtil::response($ret);                  
    }
    
} 