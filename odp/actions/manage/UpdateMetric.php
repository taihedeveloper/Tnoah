<?php
/**
*   更新指定监控项的信息
*   @author zhangweizhi@taihe.com
*/
class Action_UpdateMetric extends Ap_Action_Abstract {

    public function execute() {

        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];

        $id       = $param['id'];
        $name     = $param['name'];
        $tag      = $param['tag'];
        $status   = $param['status'];
        $add_user = isset($param['add_user']) ? $param['add_user'] : '';
        $addition = isset($param['addition']) ? $param['addition'] : '';

        if(!isset($id) || !isset($name) || !isset($tag) || !isset($status)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }
        $id     = intval($param['id']);
        $status = intval($param['status']);
        
        if($id <= 0) {
            Tnoah_OutPutUtil::responseParamError();                                    
        }
        if($status != 0 && $status != 1) {
            Tnoah_OutPutUtil::responseParamError();                                                
        }

        $validParams = array('CPU', 'MEM', 'DISK', 'DISK_IO', 'NET');
        if(!in_array($tag, $validParams)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }

        $metricObj = Service_LibFactory::getInstance('Service_Metric');
        $infos = $metricObj->updateMetric($id, $name, $tag, $status, $add_user, $addition);

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

             