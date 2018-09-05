<?php
/**
*   更新模板信息的信息
*   @author dupeng@taihe.com
*/
class Action_UpdateTemplate extends Ap_Action_Abstract {

    public function execute() {

        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];

        $tpl_id       = $param['tpl_id'];
        $tpl_name     = $param['tpl_name'];
        $uic = $param['uic'];

        if(!isset($tpl_id)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }

        if(!isset($tpl_name) && !isset($uic)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }
        
        $tpl_id     = intval($param['tpl_id']);
        if($tpl_id <= 0) {
            Tnoah_OutPutUtil::responseParamError();                                    
        }
        
        $updateArr = array(
            'tpl_name' => $tpl_name,
            'tpl_id' => $tpl_id,
            'uic' => $uic,
        ); 

        $tplObj = Service_LibFactory::getInstance('Service_Template');
        $infos = $tplObj->updateTemplate($updateArr);

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

             