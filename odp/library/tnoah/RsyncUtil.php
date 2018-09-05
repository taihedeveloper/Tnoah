<?php
/**
 * @name Util_OutPutUtil
 * @desc 公共输出类
 * @author ydp
 */

class Tnoah_RsyncUtil
{
    public function execute($ips,$file_src_dir,$file_dst_dir,$dst_user, &$err_cmd) {

        $ip_array=explode(",",$ips);
        foreach($ip_array as $value){
            // $cmd_str = "rsync -avz " .$file_src_dir ." ".$dst_user."@".$value.":".$file_dst_dir ;
            $cmd_str = "scp -o StrictHostKeyChecking=no " .$file_src_dir ." ".$dst_user."@".$value.":".$file_dst_dir;
	    // echo $cmd_str;die;
            $array= array();
            $ret = exec($cmd_str,$array,$status);
	    if($status !=0){
		// echo $cmd_str;die;
		$err_cmd = $cmd_str;
                return false;  ///// 错误机器 
            }
        }
        return true;


    }

}
