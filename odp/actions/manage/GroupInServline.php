<?php
/**
*   判断集群是否在指定的业务线下
*   @param1 string group_name  
*   @param2 intval serviceline_id  
*   @author zhangweizhi@taihe.com
*/
class Action_GroupInServline extends Ap_Action_Abstract {

    public function execute() {
        header("Content-type:text/html;charset=utf-8");
        // 获取参数
        $arrRequest       = Saf_SmartMain::getCgi();
        $param            = $arrRequest['request_param'];
        $service_line_id  = intval($param['service_line_id']);
        $group_name       = trim($param['group_name']);
        
        if($service_line_id <= 0) {
            Tnoah_OutPutUtil::responseParamError();                                                            
        }
        if(!isset($group_name) || empty($group_name)) {
            Tnoah_OutPutUtil::responseParamError();                                                                        
        }
        $groupObj  = Service_LibFactory::getInstance('Service_Group');      
        $infos = $groupObj->getGroupByName($group_name);
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
        if(intval($infos[0]['service_line_id']) == $service_line_id) {
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