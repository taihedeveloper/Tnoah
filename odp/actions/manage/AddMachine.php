<?php
/**
*   增加机器
*   @author zhangweizhi@taihe.com
*/
class Action_AddMachine extends Ap_Action_Abstract {

    public function execute() {
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];

        $ipAddr          = $param['ip_addr'];
        $name            = $param['name'];
        $groupId         = $param['group_id'];

        if(!isset($ipAddr) || !isset($name) || !isset($groupId) ) {
            Tnoah_OutPutUtil::responseParamError();                                    
        }
        $groupObj  = Service_LibFactory::getInstance('Service_Machine');
        $infos     = $groupObj->addMachine($ipAddr, $name, $groupId);
        
        if(NULL === $infos || false === $infos) {
            Tnoah_OutPutUtil::responseSqlError();                        
        }

        $ret = array(
            'error_code' => 22000,
            'data'       => $infos,
        );
        Tnoah_OutPutUtil::response($ret);                
    }
}