<?php
/**
*   获取所有的集群的报警信息
*   @author zhangweizhi@taihe.com
*/
class Action_GetEventShow extends Ap_Action_Abstract {

    public function execute() {

        $showObj  = Service_LibFactory::getInstance('Service_Show');
        // 需要加参数
        $infos = $showObj->getEventShowInfos();
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