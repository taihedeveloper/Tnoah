<?php
/**
*   获取集群下配置
*   @author zhangweizhi@taihe.com
*/
class Action_GetGroupConf extends Ap_Action_Abstract {

    public function execute() {
        // 获取参数
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];
        $groupId    = intval($param['group_id']);
        $tag        = $param['tag'];

        if($groupId <= 0) {
            Tnoah_OutPutUtil::responseParamError();                                                
        }        
        
        $groupObj  = Service_LibFactory::getInstance('Service_Group');      
        $infos = $groupObj->getGroupConf($groupId);

        if($tag == 'BUSINESS') {
            foreach($infos as $key => $val) {
                if($val['type'] != 'log') {
                    unset($infos[$key]);
                }
            }
        }
        if($tag == 'PROC') {
            foreach($infos as $key => $val) {
                if($val['type'] != 'proc') {
                    unset($infos[$key]);
                }
            }
        }
        if($tag == 'PORT') {
            foreach($infos as $key => $val) {
                if($val['type'] != 'port') {
                    unset($infos[$key]);
                }
            }
        }

	// 格式转换
	$retInfos = array();
	foreach($infos as $key => $val) {
	    $retInfos[] = $val;
	}	

        $ret = array(
            'error_code' => 22000,
            'data'       => $retInfos,
        );
        Tnoah_OutPutUtil::response($ret);                
    }
}