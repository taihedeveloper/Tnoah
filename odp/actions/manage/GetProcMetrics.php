<?php
/**
*   获取进程类的监控项
*   @author zhangweizhi@taihe.com
*/
class Action_GetProcMetrics extends Ap_Action_Abstract {
    public function execute() {
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];
        $groupId    = intval($param['group_id']);
        if($groupId <= 0) {
            Tnoah_OutPutUtil::responseSqlError();                                    
        }
        
        $groupObj = Service_LibFactory::getInstance('Service_Group');
        $infos = $groupObj->getGroupById($groupId);
        if(NULL === $infos || false == $infos) {
            Tnoah_OutPutUtil::responseSqlError();                                    
        }

        $confArr          = json_decode($infos[0]['config'], true);
        $procConfArr = $confArr['proc'];
        $ret = array(
            'error_code' => 22000,
            'data'       => $procConfArr,
        );
        Tnoah_OutPutUtil::response($ret);     
    }
}