<?php
/**
*   根据当前机器获取topic
*   @param  ip_addr
*   @author zhangweizhi@taihe.com
*/
class Action_GetTopicName extends Ap_Action_Abstract {

    public function execute() {
        header("Content-type:text/html;charset=utf-8");
        // 获取参数 && 参数验证
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];
        $ip_addr    = trim($param['ip_addr']);
        
        if(empty($ip_addr)) {
            Tnoah_OutPutUtil::responseParamError();                                                            
        }
	// 策略: tnoah_topic_x   x为ip地址转换为整数后, 再对其用 deli 取模后得到的数字
	$deli   = intval(Bd_Conf::getAppConf('dest/deli'));
	$ipLong = ip2long($ip_addr);
	$tail   = strval($ipLong % $deli);

	// $file_name = '/home/work/odp/data/tnoah_tagent_' . $tail . '/tagent.tar.gz';
	$file_name = '/home/work/odp/data/tnoah_topic_api_tagent/tagent.tar.gz';
	$fp = fopen($file_name, 'r+');
	$file_size = filesize($file_name);
	//返回的文件
        Header("Content-type: application/octet-stream");
        //按照字节格式返回
        Header("Accept-Ranges: bytes");
        //返回文件大小
        Header("Accept-Length: ".$file_size);
        //弹出客户端对话框，对应的文件名
        Header("Content-Disposition: attachment; filename=tagent.tar.gz");
	
	$buffer = 8192;
	while(!feof($fp)) {
	     $file_data = fread($fp, $buffer);
	     echo $file_data;
	}
	fclose($fp);

	// // 后续加针对不同集群下机器的多个topic版本
        // $ret = array(
        //     'error_code' => 22000,
        //     'data'       => $topic_name,
        // );
        // Tnoah_OutPutUtil::response($ret);                
    }
}
