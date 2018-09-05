<?php
/**
*   增加集群下配置
*   @param  tag: BUSINESS/ PROC/ PORT
*   @author zhangweizhi@taihe.com
*/
class Action_AddGroupConf extends Ap_Action_Abstract {

    public function execute() {
        header("Content-type:text/html;charset=utf-8");
        // 获取参数 && 参数验证
        $arrRequest       = Saf_SmartMain::getCgi();
        $param            = $arrRequest['request_param'];
        $group_id         = intval($param['group_id']);        
        $tag              = trim($param['tag']);
        $name             = trim($param['name']);
        $val              = trim($param['val']);
        $log_path         = trim($param['log_path']);
        $log_style        = trim($param['log_style']);
        $log_format       = trim($param['log_format']);
        $match_type       = trim($param['match_type']);   // awk_str / match_str / search_str
	$proc_deeply_path = trim($param['proc_deeply_path']);	// 进程详情
	$filt_str         = trim($param['filt_str']);

        $log_eg = trim($param['log_eg']);
	$need_check       = intval(trim($param['need_check']));
	if($need_check == 0) {
		$need_check = false;
	}else{
		$need_check = true;
	}
        
        if($group_id <= 0 ){
            Tnoah_OutPutUtil::responseParamError();                                                
        }

        if(empty($tag) || empty($name) || empty($val)) {
            Tnoah_OutPutUtil::responseParamError();                                                            
        }

        $valid_params = array('BUSINESS', 'PROC', 'PORT');
        if(!in_array($tag, $valid_params)) {
            Tnoah_OutPutUtil::responseParamError();                                                            
        }
        if($tag == 'BUSINESS' && (empty($log_path) || empty($log_style) || empty($match_type))) {
            Tnoah_OutPutUtil::responseParamError();                                                                        
        }
        if($tag == 'BUSINESS' && $log_style == 'newly' && empty($log_format)) {
            Tnoah_OutPutUtil::responseParamError();            
        }

        if(!empty($log_style) && $log_style != 'newly' && $log_style != 'split' ) {
            Tnoah_OutPutUtil::responseParamError();                        
        }

        if(!empty($match_type) && $match_type != 'awk_str' && $match_type != 'match_str' && $match_type != 'search_str') {
            Tnoah_OutPutUtil::responseParamError();                                    
        }
        /*$tag = BUSINESS
        val
        match_type 正则 或 按列匹配
        */
        if($need_check && $tag == "BUSINESS"){
            if($match_type == "match_str" && $match_type == "search_str"){
                $match_ret =  preg_match("/".$val."/", $log_eg, $matches);
                if($match_ret !== 1){
                    $temp_ret = array(
                        'error_code' => 22001,
                        'error_msg'       => "match error",
                    );
                    Tnoah_OutPutUtil::response($temp_ret);
                }
            }elseif($match_type == "awk_str"){

            }
        }
        //end
        
        $groupObj  = Service_LibFactory::getInstance('Service_Group');      
	if($log_style == 'newly' && !strpos($log_path, '{}')) {
		Tnoah_OutPutUtil::responseParamError();            
	}
	
        $infos = $groupObj->addGroupConf($group_id, $tag, $name, $val, $log_path, $log_style, $log_format, $match_type, $proc_deeply_path, $filt_str); 
        
        if(NULL === $infos || false === $infos) {
            Tnoah_OutPutUtil::responseSqlError();            
        }
        $ret = array(
            'error_code' => 22000,
            'data'       => $infos,
        );
        Tnoah_OutPutUtil::response($ret);                
    }
}