<?php
/**
*   获取全部业务线
*   @author zhangweizhi@taihe.com
*/
class Action_GetAllServiceLine extends Ap_Action_Abstract {

    public function execute() { 
        $serviceObj = Service_LibFactory::getInstance('Service_ServiceLine');
        $infos = $serviceObj->getAllServiceLine(); 
        if(NULL === $infos || false === $infos) {
            Tnoah_OutPutUtil::responseSqlError();
        }
        $ret = array(
            'error_code' => 22000,
            'data'      => $infos,
        );
        Tnoah_OutPutUtil::response($ret);
    }
}