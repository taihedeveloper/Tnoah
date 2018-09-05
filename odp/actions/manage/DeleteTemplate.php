<?php
/**
*   删除某个集群下的模板
*   @author dupeng@taihe.com
*/
class Action_DeleteTemplate extends Ap_Action_Abstract {
    
        public function execute() {

            $arrRequest = Saf_SmartMain::getCgi();
            $param      = $arrRequest['request_param'];
    
            $tpl_id = intval($param['tpl_id']);
            $group_id = intval($param['group_id']);
    
            if(!isset($tpl_id) || !isset($group_id)) {
                Tnoah_OutPutUtil::responseParamError();                        
            }
            if($tpl_id <= 0) {
                Tnoah_OutPutUtil::responseParamError();                                    
            }
    
            $tplObj    = Service_LibFactory::getInstance('Service_Template');
            $deleteArr = array(
                    'group_id' => $group_id,
                    'tpl_id'   => $tpl_id,
                );
            $deleteInfos = $tplObj->deleteTemplate($deleteArr);
    
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