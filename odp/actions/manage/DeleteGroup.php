<?php
/**
*   删除指定集群
*   @author zhangweizhi@taihe.com
*/
class Action_DeleteGroup extends Ap_Action_Abstract {

    public function execute() {

        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];

        $id = $param['id'];

        if(!isset($id)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }

        $groupObj  = Service_LibFactory::getInstance('Service_Group');
        $deleteInfos = $groupObj->deleteGroup($id);

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