<?php
/**
 * @name Action_RsyncUtil
 * @desc 给其他机器同步文件
 * @author ydp
 */
class Action_RsyncFile extends Ap_Action_Abstract {
	public function execute() {

		$arrRequest = Saf_SmartMain::getCgi();
		$arrInput = $arrRequest['get'];
		$ips = $arrInput["ips"];
		$file_src_dir = $arrInput["file_src_dir"];
		$file_dst_dir = $arrInput["file_dst_dir"];
		$dst_user = $arrInput["dst_user"];

		$ip_array=explode(",",$ips);
		foreach($ip_array as $value){
			$cmd_str = "rsync -avz " .$file_src_dir ." ".$dst_user."@".$value.":".$file_dst_dir ;
			$array= array();
			$ret = exec($cmd_str,$array,$status);
			if($status !=0){
				$result = array();
				$result["error"] = 22001;
				Tnoah_OutPutUtil::response($result);
			}
		}
		$result = array();
		$result["error"] = 22000;
		Tnoah_OutPutUtil::response($result);


	}
}