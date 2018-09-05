<?php
/**
*   获取指定业务线下集群的信息
*   @author zhangweizhi@taihe.com
*/
class Action_GetGroupInfos extends Ap_Action_Abstract {

    public function execute() {

        $arrRequest    = Saf_SmartMain::getCgi();
        $param         = $arrRequest['request_param'];
        $serviceLineId = $param['service_line_id'];
        if(!isset($serviceLineId)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }
        $serviceLineId = intval($serviceLineId);
        if($serviceLineId <= 0) {
            Tnoah_OutPutUtil::responseParamError();                                    
        }

        $groupObj  = Service_LibFactory::getInstance('Service_Group');
        $infos = $groupObj->getGroupInfos($serviceLineId);

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