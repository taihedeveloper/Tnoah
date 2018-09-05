<?php
/**
*   获取集群下的全部监控项名称
*   @author zhangweizhi@taihe.com
*/
class Action_GetGroupMetricsName extends Ap_Action_Abstract {

    public function execute() {

        $arrRequest    = Saf_SmartMain::getCgi();
        $param         = $arrRequest['request_param'];
        $groupId       = intval($param['group_id']);
        if($groupId <= 0) {
            Tnoah_OutPutUtil::responseParamError();            
        }

        $names = array();
        // 性能监控项名称
        $metricObj  = Service_LibFactory::getInstance('Service_Metric');
        $natureInfos = $metricObj->getAllNatureInfos();
        $tagArr = array();
        foreach($natureInfos as $k => $v) {
            // $tagArr[$v['tag']][] = $v['metric_name'];
            // $tagArr[$v['tag']][] = $v['addition'];
	    $tagArr[$v['tag']][] = array(
		'name' => $v['metric_name'],
		'addi' => $v['addition'],
  	    );
        }
        $names = array_merge($names, $tagArr);

        // 业务、端口、进程监控项名称
        $groupObj   = Service_LibFactory::getInstance('Service_Group');
        $groupInfos = $groupObj->getGroupById($groupId)[0]['config'];
        $groupInfos = json_decode($groupInfos, true);
        
        $logInfos  = $groupInfos['log'];
        $procInfos = $groupInfos['proc'];
        $portInfos = $groupInfos['port'];


        $tagArr = array();
        // 业务
        foreach($logInfos as $k => $v) {
            $confArr = $v['conf'];
            foreach($confArr as $key => $val) {
                $tagArr[] = array(
		    'name' => $val['item_name_prefix'],
                    'addi' => '',
                );
		if(array_key_exists('search_str', $val) && $val['search_str'] != '') {
                    $tagArr[] = array(
		        'name' => $val['item_name_prefix'] . '_max',
                        'addi' => '',
		    );
		}
            }
        }
        $names['BUSINESS'] = $tagArr;

        $tagArr = array();        
        // 进程
        foreach($procInfos as $k => $v) {
                $tagArr[] = array(
		    'name' => $v['item_name_prefix'],
                    'addi' => '',
                );
        }
	// 进程(性能类)
	foreach($procInfos as $k => $v) {
		if($v['proc_deeply_path'] != "") {
                    $tagArr[] = array(
		        'name' => $v['item_name_prefix']."_cpurate",
                        'addi' => '',
                    );
                    $tagArr[] = array(
		        'name' => $v['item_name_prefix']."_memrate",
                        'addi' => '',
                    );
                    $tagArr[] = array(
		        'name' => $v['item_name_prefix']."_fdnum",
                        'addi' => '',
                    );
		}
	}
        $names['PROCESS'] = $tagArr;        

        $tagArr = array();        
        // 端口
        foreach($portInfos as $k => $v) {
                $tagArr[] = array(
		    'name' => $v['item_name_prefix'],
                    'addi' => '',
                );
        }   
        $names['PORT'] = $tagArr;        

        $ret = array(
            'error_code' => 22000,
            'data' => $names,
        );
    
        Tnoah_OutPutUtil::response($ret);            
    }
}
