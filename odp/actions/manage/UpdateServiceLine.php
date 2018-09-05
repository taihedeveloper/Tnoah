<?php
/**
*   更改指定的业务线
*   @author zhangweizhi@taihe.com
*/
class Action_UpdateServiceLine extends Ap_Action_Abstract {

    public function execute() {
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];

        $id              = $param['id'];
        $serviceLineName = $param['service_line_name'];
        if(!isset($id) || !isset($serviceLineName)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }

        $serviceObj  = Service_LibFactory::getInstance('Service_ServiceLine');
        $updateInfos = $serviceObj->updateServiceLine($id, $serviceLineName);

        if(NULL === $updateInfos) {
            Tnoah_OutPutUtil::responseSqlError();                        
        }
        $ret = array(
            'error_code' => 22000,
            'data'       => $updateInfos,
        );
        Tnoah_OutPutUtil::response($ret);                
    }
}