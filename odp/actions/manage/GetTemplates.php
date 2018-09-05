<?php
/**
*   根据集群id获取所有模板信息息
*   @author dupeng@taihe.com
*/
class Action_GetTemplates extends Ap_Action_Abstract {

    public function execute() {

        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];
        $groupId    = $param['group_id'];
        if(!isset($groupId)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }
        
        $groupId = intval($groupId);
        if($groupId <= 0) {
            Tnoah_OutPutUtil::responseParamError();                                    
        }

        $tplObj  = Service_LibFactory::getInstance('Service_Template');
        $infos = $tplObj->getTemplates($groupId);
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