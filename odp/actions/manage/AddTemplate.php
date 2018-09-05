<?php
/**
*   向集群添加模板信息
*   @author dupeng@taihe.com
*/
class Action_AddTemplate extends Ap_Action_Abstract {

    public function execute() {
        // public function addMetric($name, $tag, $status, $add_user, $addition)
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];

        $group_id   = $param['group_id'];
        $tpl_name   = $param['tpl_name'];
        $create_user   = isset($param['create_user']) ? ($param['create_user']) : 'system';
        $create_at = time();
        $uic = $param['uic'];;

        if(!isset($tpl_name) || !isset($group_id)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }

        $paramArr = array(
            'group_id' => $group_id,
            'tpl_name' => $tpl_name,
            'create_user' => $create_user,
            'create_at' => $create_at,
            'uic' => $uic,
        );
        $tplObj  = Service_LibFactory::getInstance('Service_Template');
        $infos = $tplObj->addTemplate($paramArr);
        if(NULL === $infos) {
            Tnoah_OutPutUtil::responseSqlError();                        
        }
        $ret = array(
            'error_code' => 22000,
            'data'       => array(
                    'tpl_id' =>$infos,
                    'tpl_name' => $tpl_name,
                    'create_user' => $create_user,
                    'create_at' => $create_at,
                    'uic' => $uic,
                ),
        );
        Tnoah_OutPutUtil::response($ret);                
    }
}