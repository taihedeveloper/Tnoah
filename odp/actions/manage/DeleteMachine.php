<?php
/**
*   删除指定机器
*   @author zhangweizhi@taihe.com
*/
class Action_DeleteMachine extends Ap_Action_Abstract {

    public function execute() {
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];

        $id = intval($param['id']);

        if(!isset($id)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }
        if($id <= 0) {
            Tnoah_OutPutUtil::responseParamError();                                    
        }

        $groupObj    = Service_LibFactory::getInstance('Service_Machine');
        $deleteInfos = $groupObj->deleteMachine($id);

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