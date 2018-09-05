<?php
/**
*   获取端口类的监控项
*   @author zhangweizhi@taihe.com
*/
class Action_GetPortMetrics extends Ap_Action_Abstract {

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

        $confArr     = json_decode($infos[0]['config'], true);
        $portConfArr = $confArr['port'];

        $ret = array(
            'error_code' => 22000,
            'data'       => $portConfArr,
        );
        Tnoah_OutPutUtil::response($ret);                
    }

}