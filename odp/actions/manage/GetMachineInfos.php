<?php
/**
*   获取指定集群下机器的信息
*   @author zhangweizhi@taihe.com
*/
class Action_GetMachineInfos extends Ap_Action_Abstract {

    public function execute() {

        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];
        $groupId    = $param['group_id'];
        if(!isset($groupId)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }
        
        $groupId = intval($groupId);
        if($groupId <= 0) {
            Tnoah_OutPutUtil::responseParamError();                                    
        }

        $groupObj  = Service_LibFactory::getInstance('Service_Machine');
        $infos = $groupObj->getMachineInfos($groupId);
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
