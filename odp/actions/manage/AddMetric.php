<?php
/**
*   增加监控项
*   @author zhangweizhi@taihe.com
*/
class Action_AddMetric extends Ap_Action_Abstract {

    public function execute() {
        // public function addMetric($name, $tag, $status, $add_user, $addition)
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];

        $name     = $param['name'];
        $tag      = $param['tag'];
        $status   = isset($param['status']) ? intval($param['status']) : 0;
        $add_user = isset($param['add_user']) ? $param['add_user'] : '';
        $addition = isset($param['addition']) ? $param['addition'] : '';

        if(!isset($name) || !isset($tag)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }
        if($status != 0 && $status != 1) {
            Tnoah_OutPutUtil::responseParamError();                                    
        }
        
        $validParams = array('CPU', 'MEM', 'DISK', 'DISK_IO', 'NET');
        if(!in_array($tag, $validParam)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }

        $groupObj  = Service_LibFactory::getInstance('Service_Metric');
        $infos = $groupObj->addMetric($name, $tag, $status, $add_user, $addition);

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