<?php
/**
*   更新指定集群的信息
*   @author zhangweizhi@taihe.com
*/
class Action_UpdateGroup extends Ap_Action_Abstract {

    public function execute() {
        $arrRequest      = Saf_SmartMain::getCgi();
        $param           = $arrRequest['request_param'];

        $id              = intval($param['id']);
        $groupName       = $param['group_name'];
        $serviceLineId   = intval($param['service_line_id']);
        $serviceLineName = $param['service_line_name'];
        $config          = $param['config'];
        $remarks         = $param['remarks'];    // 备注   
        
        if(!isset($id)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }
        if($id <= 0 ) {
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
        $infos = $groupObj->updateGroup($id, $groupName, $serviceLineId, $serviceLineName, $config, $remarks);
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

             