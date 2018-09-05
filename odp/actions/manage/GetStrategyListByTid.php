<?php
/**
*   通过模板id获取用户的所有报警策略list
*   @author zhangweizhi@taihe.com
*/
class Action_Getstrategylist extends Ap_Action_Abstract {

    public function execute() {

        $arrRequest    = Saf_SmartMain::getCgi();
        $param         = $arrRequest['request_param'];
        $tpl_id = $param['tpl_id'];
        if(!isset($tpl_id)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }
        $tpl_id = intval($tpl_id);
        if($tpl_id <= 0) {
            Tnoah_OutPutUtil::responseParamError();                                    
        }

        $groupObj  = Service_LibFactory::getInstance('Service_Strategy');
        $infos = $groupObj->getStrategylistByTid($tpl_id);

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