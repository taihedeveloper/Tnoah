<?php
/**
*   新增业务线
*   @author zhangweizhi@taihe.com
*/
class Action_AddServiceLine extends Ap_Action_Abstract {

    public function execute() {
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];

        $serviceLineName = $param['service_line_name'];
        if(!$serviceLineName) {
            Tnoah_OutPutUtil::responseParamError();            
        }
        $serviceObj = Service_LibFactory::getInstance('Service_ServiceLine');    

        $insertInfos = $serviceObj->addServiceLine($serviceLineName);

        if(NULL === $insertInfos || false === $insertInfos) {
            Tnoah_OutPutUtil::responseSqlError();            
        }
        $ret = array(
            'error_code' => 22000,
            'data'       => $insertInfos,
        );
        Tnoah_OutPutUtil::response($ret);        
    }
}
