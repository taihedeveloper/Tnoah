<?php
/**
*   删除指定的业务线
*   @author zhangweizhi@taihe.com
*/
class Action_DeleteServiceLine extends Ap_Action_Abstract {

    public function execute() {
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];
        $id         = $param['id'];
        if(!isset($id)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }
        $id = intval($id);
        if($id <= 0) {
            Tnoah_OutPutUtil::responseParamError();            
        }

        $serviceObj  = Service_LibFactory::getInstance('Service_ServiceLine');
        $deleteInfos = $serviceObj->deleteServiceLine($id);
        if(NULL === $deleteInfos) {
            Tnoah_OutPutUtil::responseSqlError();                        
        }
        $ret = array(
            'error_code' => 22000,
            'data'       => $deleteInfos,
        );
        Tnoah_OutPutUtil::response($ret);    
    }
}