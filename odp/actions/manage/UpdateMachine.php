<?php
/**
*   更新指定机器的信息
*   @author zhangweizhi@taihe.com
*/
class Action_UpdateMachine extends Ap_Action_Abstract {

    public function execute() {

        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];

        $id              = $param['id'];
        $ipAddr          = $param['ip_addr'];
        $name            = $param['name'];
        $groupId         = $param['group_id'];

        if(!isset($id) ) {
            Tnoah_OutPutUtil::responseParamError();                                    
        }
        $id = intval($id);
        if($id <= 0) {
            Tnoah_OutPutUtil::responseParamError();                                                
        }

        $groupObj  = Service_LibFactory::getInstance('Service_Machine');
        $infos = $groupObj->updateMachine($id, $ipAddr, $name, $groupId);

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

             