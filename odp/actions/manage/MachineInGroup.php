<?php
/**
*   判断集群是否在指定的业务线下
*   @param1 string machine_name  
*   @param2 intval group_id  
*   @author zhangweizhi@taihe.com
*/
class Action_MachineInGroup extends Ap_Action_Abstract {

    public function execute() {
        header("Content-type:text/html;charset=utf-8");

        // 获取参数
        $arrRequest         = Saf_SmartMain::getCgi();
        $param              = $arrRequest['request_param'];
        $group_id           = intval($param['group_id']);
        $machine_name       = trim($param['machine_name']);
        
        if($group_id <= 0) {
            Tnoah_OutPutUtil::responseParamError();                                                            
        }
        if(!isset($machine_name) || 0 == strlen($machine_name)) {
            Tnoah_OutPutUtil::responseParamError();                                                                        
        }


        $machineObj  = Service_LibFactory::getInstance('Service_Machine');      
        $infos = $machineObj->getMachineByName($machine_name);

        if(NULL === $infos || false === $infos) {
            Tnoah_OutPutUtil::responseSqlError();                        
        }
        if(empty($infos)) {
            Tnoah_OutPutUtil::response(array(
                'error_code' => 22000,
                'data'       => array(),
            ));                
        }
        $data = 0;
        if(intval($infos[0]['group_id']) == $group_id) {
            $data = $infos;
        }else {
            $data = array();
        }
        Tnoah_OutPutUtil::response(array(
            'error_code' => 22000,
            'data'       => $data,
        ));  
                  
    }
}