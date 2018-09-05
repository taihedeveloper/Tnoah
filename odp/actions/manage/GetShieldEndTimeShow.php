<?php
/**
*   获取报警屏蔽结束时间，根据show_id获取信息
*   @author gongpulin@taihe.com
*/
class Action_GetShieldEndTimeShow extends Ap_Action_Abstract {

    public function execute() {
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];
        $show_id       = $param['show_id'];   // must
        if(!isset($show_id)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }

        $showObj  = Service_LibFactory::getInstance('Service_Show');
        // 需要加参数
        $infos = $showObj->getShieldEndTimeShow($show_id);
        // var_dump($infos);
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