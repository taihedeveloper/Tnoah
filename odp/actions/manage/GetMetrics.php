<?php
/**
*   获取全部监控项
*   @author zhangweizhi@taihe.com
*/
class Action_GetMetrics extends Ap_Action_Abstract {

    public function execute() {

        $metricObj  = Service_LibFactory::getInstance('Service_Metric');
        // 需要加参数
        $infos = $metricObj->getMetricInfos();
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