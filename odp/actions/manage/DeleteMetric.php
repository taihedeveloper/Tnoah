<?php
/**
*   删除指定匹配项
*   @author zhangweizhi@taihe.com
*/
class Action_DeleteMetric extends Ap_Action_Abstract {
    
        public function execute() {

            $arrRequest = Saf_SmartMain::getCgi();
            $param      = $arrRequest['request_param'];
    
            $id = intval($param['id']);
    
            if(!isset($id)) {
                Tnoah_OutPutUtil::responseParamError();                        
            }
            if($id <= 0) {
                Tnoah_OutPutUtil::responseParamError();                                    
            }
    
            $metricObj    = Service_LibFactory::getInstance('Service_Metric');
            $deleteInfos = $metricObj->deleteMetric($id);
    
            if(NULL === $deleteInfos) {
                Tnoah_OutPutUtil::responseSqlError();                        
            }
            $ret = array(
                'error_code' => 22000,
                'data'       => $deleteInfos,
            );
            Tnoah_OutPutUtil::response($ret);                
        }
    }