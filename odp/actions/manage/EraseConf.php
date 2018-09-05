<?php
/**
*   删除配置  (根据ip 和 集群id)
*   @author zhangweizhi@taihe.com
*/
class Action_EraseConf extends Ap_Action_Abstract {

    public function execute() {
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];
        $ip_addr  = trim($param['ip_addr']);
	$group_id = trim($param['group_id']);
        if(!isset($ip_addr) || !isset($group_id)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }
	if(empty($ip_addr) || intval($group_id) <= 0) {
            Tnoah_OutPutUtil::responseParamError();            
	}

        $serviceObj  = Service_LibFactory::getInstance('Service_Machine');
        $deleteInfos = $serviceObj->eraseMachineConf($ip_addr, $group_id);
        if(true != $deleteInfos) {
            Tnoah_OutPutUtil::responseSqlError();                        
        }
        $ret = array(
            'error_code' => 22000,
        );
        Tnoah_OutPutUtil::response($ret);    
    }
}
