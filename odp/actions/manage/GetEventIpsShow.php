<?php
/**
*   获取该集群对应的监控指标报警的机器ip，根据show_id获取信息
*   @author gongpulin@taihe.com
*/
class Action_GetEventIpsShow extends Ap_Action_Abstract {

    public function execute() {
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];
        $showId       = $param['show_id'];   // must
        if(!isset($showId)) {
            Tnoah_OutPutUtil::responseParamError();                        
        }

        $redis = new Redis();
        $redis->connect('***.***.***.***', 6381); // redis地址
        $infos = $redis->get($showId);
        $redis->close();
        // var_dump($infos);
        
        $ret = array(
            'error_code' => 22000,
            'data'       => $infos,
        );
        Tnoah_OutPutUtil::response($ret);            
    }
}