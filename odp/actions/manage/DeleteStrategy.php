<?php
/**
*   根据策略id，删除策略信息
*   @author dupeng@taihe.com
*/
class Action_DeleteStrategy extends Ap_Action_Abstract {

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

        $strObj    = Service_LibFactory::getInstance('Service_Strategy');
        $deleteInfos = $strObj->deleteStrategy($id);

        if(NULL === $deleteInfos || false === $deleteInfos) {
            Tnoah_OutPutUtil::responseSqlError();                        
        }
        $ret = array(
            'error_code' => 22000,
            'data'       => $deleteInfos,
        );
        Tnoah_OutPutUtil::response($ret);                
    }
}