<?php
/**
*   复制策略到响应的集群
*   @author dupeng@taihe.com
*/
class Action_CopyStrategy extends Ap_Action_Abstract {

    public function execute() {
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];

        $group_id              = $param['group_id'];
        $tpl_id            = $param['tpl_id'];
        $is_all          = $param['is_all'];


        if(!isset($tpl_id) ) {
            Tnoah_OutPutUtil::responseParamError();                                    
        }
        $tpl_id = intval($tpl_id); 
        if($tpl_id <= 0 ) {
            Tnoah_OutPutUtil::responseParamError();                                                
        }
        //两个必须要有一个
        if(empty($group_id)  && empty($is_all)){
            Tnoah_OutPutUtil::responseParamError();
        }

        $allGroupIds = "";
        $allGroupArray = array();
        $serviceLineObj  = Service_LibFactory::getInstance('Service_ServiceLine');
        $allLines = $serviceLineObj->getAllServiceLine();
        $groupObj  = Service_LibFactory::getInstance('Service_Group');

        //建立group_id与产品线的映射关系，后面要使用
        $groupidToProductMapping = array(); 
        if($allLines){
            foreach ($allLines as $key => $value) {
                $lineid = $value["id"];
                $productName = $value['service_line_name'];
                $infos = $groupObj->getGroupInfos($lineid);
                foreach ($infos as $key => $value) {
                    $groupId = $value["id"];
                    $allGroupArray[] = $groupId;
                    $groupidToProductMapping[$groupId] = $productName ;
                }
            }
            
        }
        if($is_all && $is_all == '1'){
            $allGroupIds = implode(',', $allGroupArray);
        }else{
            $allGroupIds = $group_id;
        }
        $strObj  = Service_LibFactory::getInstance('Service_Strategy');
        $strategyArr = array(
                'tpl_id' => $tpl_id,
                'group_id' => $allGroupIds,
            );
        $wrongId = array();
        $strObj  = Service_LibFactory::getInstance('Service_Strategy');
        $infos = $strObj->copyStrategy($strategyArr,$wrongId,$groupidToProductMapping);

        if(NULL === $infos) {
            Tnoah_OutPutUtil::responseSqlError();                        
        }
        if( "not all yet" === $infos){
            Tnoah_OutPutUtil::responseSqlNotAllError($wrongId);
        }
        $ret = array(
            'error_code' => 22000,
            'data'       => $infos,
        );
        Tnoah_OutPutUtil::response($ret);                
    }
}

             