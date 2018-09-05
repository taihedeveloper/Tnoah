<?php
/**
*   获据策略id，获取策略明细信息
*   @author dupeng@taihe.com
*/
class Action_GetStrategy extends Ap_Action_Abstract {

    public function execute() {

        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];
        $id    = $param['id'];
        if(!isset($id)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }
        
        $id = intval($id);
        if($id <= 0) {
            Tnoah_OutPutUtil::responseParamError();                                    
        }

        $strategyObj  = Service_LibFactory::getInstance('Service_Strategy');
        $infos = $strategyObj->getStrategy($id);
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