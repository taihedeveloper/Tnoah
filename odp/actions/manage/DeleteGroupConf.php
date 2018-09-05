<?php
/**
*   删除集群下配置
*   @author zhangweizhi@taihe.com
*/
class Action_DeleteGroupConf extends Ap_Action_Abstract {

    public function execute() {
        header("Content-type:text/html;charset=utf-8");
        // 获取参数 && 参数验证
        $arrRequest = Saf_SmartMain::getCgi();
        $param           = $arrRequest['request_param'];
        $groupId         = $param['group_id'];    
        $tag        = trim($param['tag']);    
        $name       = trim($param['name']);
        $log_path   = trim($param['log_path']);
        $log_style  = trim($param['log_style']);
        // $log_format = trim($param['log_format']);
        // $match_type = trim($param['match_type']);   // awk_str / match_str
        if($groupId <= 0 ){
            Tnoah_OutPutUtil::responseParamError();                                                
        }

        if(empty($tag) || empty($name)) {
            Tnoah_OutPutUtil::responseParamError();                                                            
        }

        $valid_params = array('BUSINESS', 'PROC', 'PORT');
        if(!in_array($tag, $valid_params)) {
            Tnoah_OutPutUtil::responseParamError();                                                            
        }
        if($tag == 'BUSINESS' && (empty($log_path) || empty($log_style))) {
            Tnoah_OutPutUtil::responseParamError();                                                                        
        }
        if(!empty($log_style) && $log_style != 'newly' && $log_style != 'split' ) {
            Tnoah_OutPutUtil::responseParamError();                        
        }

        // if(!empty($match_type) && $match_type != 'awk_str' && $match_type != 'match_str' ) {
        //     Tnoah_OutPutUtil::responseParamError();                                    
        // }

        $groupObj  = Service_LibFactory::getInstance('Service_Group');      
        $infos = $groupObj->delGroupConf($groupId, $tag, $name, $log_path, $log_style);  

        if($infos == null) {
            Tnoah_OutPutUtil::response(array(
                'error_code' => 22001,
                'error_msg'  => 'delete failed',
            ));                
        }

        Tnoah_OutPutUtil::response(array(
            'error_code' => 22000,
        ));                
        

        die;

        $ipAddr          = $param['ip_addr'];
        $name            = $param['name'];
        $groupName       = $param['group_name'];
        $groupId         = $param['group_id'];
        $serviceLineName = $param['service_line_name'];
        $serviceLineId   = $param['service_line_id'];

        if(!isset($ipAddr) || !isset($name) || !isset($groupName) || !isset($groupId) || !isset($serviceLineName) || !isset($serviceLineId) ) {
            Tnoah_OutPutUtil::responseParamError();                                    
        }
        $groupId = intval($groupId);
        $serviceLineId = intval($serviceLineId);
        if($groupId <= 0 || $serviceLineId <= 0) {
            Tnoah_OutPutUtil::responseParamError();                                                
        }

        $groupObj  = Service_LibFactory::getInstance('Service_Machine');
        $infos     = $groupObj->addMachine($ipAddr, $name, $groupName, $groupId, $serviceLineName, $serviceLineId);

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