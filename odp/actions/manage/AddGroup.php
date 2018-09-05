<?php
/**
*   增加集群
*   @author zhangweizhi@taihe.com
*/
class Action_AddGroup extends Ap_Action_Abstract {

    public function execute() {
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];

        $groupName       = $param['group_name'];   // must
        $serviceLineId   = intval($param['service_line_id']);
        $serviceLineName = $param['service_line_name'];
        $config          = isset($param['config']) ? $param['config'] : '';
        $remarks         = isset($param['remarks']) ? $param['remarks'] : '';    // 备注  


        if(!isset($groupName) || !isset($serviceLineId) || !isset($serviceLineName)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }

        if($serviceLineId <= 0) {
            Tnoah_OutPutUtil::responseParamError();                                    
        }

        // 检查 config是否为json格式
        if(isset($config)) {
            if(is_null(json_decode($config, true))) {
                if($config != '') {
                    Tnoah_OutPutUtil::responseParamError();                                       
                }
            }
        }

        $groupObj  = Service_LibFactory::getInstance('Service_Group');
        $infos = $groupObj->addGroup($groupName, $serviceLineId, $serviceLineName, $config, $remarks);

        if(NULL === $infos) {
            Tnoah_OutPutUtil::responseSqlError();                        
        }
        $ret = array(
            'error_code' => 22000,
            'data'       => $infos,
        );
        Tnoah_OutPutUtil::response($ret);                
    }
}