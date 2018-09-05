<?php
/**
*   集群相关操作
*   @author zhangweizhi@taihe.com
*/
class Service_Group {

    /**
    *   @desc   删除指定集群
    *   @return int/NULL
    */
    public function deleteGroup($id) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Group');
        $where  = array(
            'id=' => $id,
        );
        $infos = $daoObj->doDelete($where);
        return $infos;
    }

    /**
    *   @desc   增加集群
    *   @return int/NULL
    */
    public function addGroup($groupName, $serviceLineId, $serviceLineName, $config, $remarks) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Group');
        $insert = array(
            'group_name'        => $groupName,
            'service_line_id'   => $serviceLineId,
            'service_line_name' => $serviceLineName,
            'config'            => $config,
            'remarks'           => $remarks,
            'sync_status'       => 0,
        );        
        $infos = $daoObj->doInsert($insert);
        return $infos;
    }

    /**
    *   @desc   更新指定集群
    *   @return int/NULL
    */
    public function updateGroup($id, $groupName, $serviceLineId, $serviceLineName, $config, $remarks) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Group');
        $update = array();
        if(isset($groupName)) {
            $update['group_name'] = $groupName;
        }
        if(isset($serviceLineId) && $serviceLineId > 0) {
            $update['service_line_id'] = $serviceLineId;
        }
        if(isset($serviceLineName)) {
            $update['service_line_name'] = $serviceLineName;
        }
        if(isset($config)) {
            $update['config'] = $config;
        }
        if(isset($remarks)) {
            $update['remarks'] = $remarks;
        }
        $update['sync_status'] = 0;
        $where = array(
            'id=' => $id
        );
        $infos = $daoObj->doUpdate($update, $where);
        return $infos;
    }

    /**
    *   @desc   根据指定配置更新指定集群
    *   @return int/NULL
    */
    public function updateGroupCustom($id, $updateArr) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Group');
        $where = array(
            'id=' => $id,
        );
        $infos = $daoObj->doUpdate($updateArr, $where);
        return $infos;
    }

    /**
    *   @desc   查询指定业务线下的集群
    *   @return array
    */
    public function getGroupInfos($serviceLineId) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Group');
		$select = array('*');
        $where  = array(
            'service_line_id=' => $serviceLineId,
        );
        $infos = $daoObj->doSelect($select, $where);
        return $infos;
    }


    /**
    *   @desc   根据名称查找集群
    *   @param  string group_name
    *   @return array
    */
    public function getGroupByName($group_name) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Group');
		$select = array('*');
        $where  = array(
            'group_name=' => $group_name,
        );
        $infos = $daoObj->doSelect($select, $where);
        return $infos;        
    }

    /**
    *   @desc   获取指定集群信息
    *   @return array
    */
    public function getGroupById($groupId) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Group');
		$select = array('*');
        $where  = array(
            'id=' => $groupId,
        );
        $infos = $daoObj->doSelect($select, $where);
        return $infos;
    }

    /**
    *    @desc  (新增监控项时用)根据集群id、配置项名称, 判断该集群所在业务线下是否有同名的配置项
    *    @param1  int     $groupId
    *    @param2  string  $name
    *    @return  boolean
    */
    public function isDupName($groupId, $name) {
	if($name == 'BUS_Tagent_Heartbeat') {
	    return false;
	}
        $daoObj = Dao_DaoFactory::getInstance('Dao_Group');
	$select = array('service_line_id');
	$where = array(
	    'id=' => $groupId,
        );
	$infos = $daoObj->doSelect($select, $where);
	// 获取业务线id
	$serId = 0;
	if($infos && $infos[0] && $infos[0]['service_line_id']) {
	    $serId = $infos[0]['service_line_id'];	
	}else {
	    return false;
	}
	// 根据业务线id获取业务线内的集群配置
        $select = array('config');
	$where = array(
            'service_line_id=' => $serId,
        );
	$confInfos = $daoObj->doSelect($select, $where);
	if(!is_array($confInfos)) {
            return false;
        }
	foreach($confInfos as $k => $v) {  // 遍历集群
	    $jVal = json_decode($v['config'], 1);
            $tmp = $this->_splitConfig($jVal);
	    foreach($tmp as $k1 => $v1) {// 遍历集群下的配置
	        if($v1['item_name_prefix'] == $name) {
                    return true;  // 重复
                }
	    }
        }
	return false;
    }


    /**
    *   @desc  增加集群下的配置
    *   @return array
    */
    public function addGroupConf($groupId, $tag, $name, $val, $log_path, $log_style, $log_format, $match_type, $proc_deeply_path = '', $filt_str = '') {
	// 重名校验
	$isDup = $this->isDupName($groupId, $name);
	if(true == $isDup) {
		Tnoah_OutPutUtil::responseDupNameItemError();
	}
	
        // 获取原来的配置
        $conf = $this->_getGroupConf($groupId);
        $curConf = array();
        if($tag == 'BUSINESS') {
            $curConf = $conf['log'];
            $conf['log'] = $this->_mergeConfLog($curConf, $name, $val, $log_path, $log_style, $log_format, $match_type, $filt_str);
        }else if($tag == 'PROC') {
            $curConf = $conf['proc'];
            $conf['proc'] = $this->_mergeConfPnP($curConf, $name, $val, $tag, $proc_deeply_path);
        }else if($tag == 'PORT') {
            $curConf = $conf['port'];
	    // 端口去掉数字校验, 支持配置 类似"0.0.0.0:8888"的
            // if(!is_numeric($val) ) {
            //     Tnoah_OutPutUtil::responseParamError();                                                                        
            // }
            $conf['port'] = $this->_mergeConfPnP($curConf, $name, $val, $tag);
        }else {
            Tnoah_OutPutUtil::responseParamError();                                                            
        }

        // 新的conf写入表
        $updateArr = array(
            'sync_status' => 0,
            'config'      => json_encode($conf),
        );
        $ret = $this->updateGroupCustom($groupId, $updateArr);
        return $ret;
    }

    /**
    *   @desc  修改集群下的配置
    *   @return array
    */
    public function setGroupConf($groupId, $tag, $name, $new_val, $log_path, $log_style, $match_type, $filt_str) {
        // 获取原来的配置
        $conf = $this->_getGroupConf($groupId);
        // 修改配置
        $type = '';
        switch ($tag) {
            case 'BUSINESS':
                $type = 'log';
                break;
            case 'PROC':
                $type = 'proc';
                break;
            default:
                $type = 'port';
                # code...
                break;
        }
        $dstArr = $conf[$type];
        if($type == 'proc' || $type == 'port') {
            foreach($dstArr as $key => $val) {
                
                if($val['item_name_prefix'] == $name) {
                    if($type == 'proc') {
                        $dstArr[$key]['proc_path'] = $new_val;                    
                    }else {
                        $dstArr[$key]['port_num'] = $new_val;
                    }

                    $conf[$type] = $dstArr;
                    $updateArr = array(
                        'sync_status' => 0,
                        'config'      => json_encode($conf),
                    );
                    $ret = $this->updateGroupCustom($groupId, $updateArr);
                    
                    return $ret;
                }
            }
            return null;
        }else {
            foreach($dstArr as $key => $val) {
                if($val['property']['log_path'] == $log_path && $val['property']['log_style'] == $log_style) {
                    foreach($val['conf'] as $key1 => $val1) {
                        if($val1['item_name_prefix'] == $name) {
                            unset($dstArr[$key]['conf'][$key1]);
                            $dstArr[$key]['conf'][$key1][$match_type] = $new_val;
                            $dstArr[$key]['conf'][$key1]['item_name_prefix'] = $name;
			    if($filt_str != '') {
                            	$dstArr[$key]['conf'][$key1]['filt_str'] = $filt_str;
			    }
                            
                            foreach($dstArr as $k => &$v) {
                                $tmp = array();
                                foreach($v['conf'] as $k1 => $v1) {
                                    $tmp[] = $v1;
                                }
                                $v['conf'] = $tmp;
                            }                            
                            $conf[$type] = $dstArr;
                            $updateArr = array(
                                'sync_status' => 0,
                                'config'      => json_encode($conf),
                            );
                            $ret = $this->updateGroupCustom($groupId, $updateArr);
                            return $ret;
                        }
                    }
                    
                }

            }
            return null;

        }
        return null;
    }

    /**
    *   @desc  删除集群下的配置
    *   @return array / null
    */
    public function delGroupConf($groupId, $tag, $name, $log_path, $log_style) {
        // 获取原来的配置
        $conf = $this->_getGroupConf($groupId);
        // 修改配置
        $type = '';
        switch ($tag) {
            case 'BUSINESS':
                $type = 'log';
                break;
            case 'PROC':
                $type = 'proc';
                break;
            default:
                $type = 'port';
                break;
        }
        $dstArr = $conf[$type];
        if($type == 'proc' || $type == 'port') {
            foreach($dstArr as $key => $val) {
                
                if($val['item_name_prefix'] == $name) {
                    unset($dstArr[$key]);
                    //重新索引
                    //$conf[$type] = $dstArr;
                    $conf[$type] = array_values($dstArr);
                    //echo "<pre>";print_r(array_values($dstArr));die;
                    $updateArr = array(
                        'sync_status' => 0,
                        'config'      => json_encode($conf),
                    );
                    $ret = $this->updateGroupCustom($groupId, $updateArr);
                    return $ret;
                }
            }
            return null;
        }else {
            foreach($dstArr as $key => $val) {
                if($val['property']['log_path'] == $log_path && $val['property']['log_style'] == $log_style) {
                    foreach($val['conf'] as $key1 => $val1) {
                        if($val1['item_name_prefix'] == $name) {
                            unset($dstArr[$key]['conf'][$key1]);
                            if(empty($dstArr[$key]['conf'])) {  // 删除后 'conf'为空
                                unset($dstArr[$key]);
                            }
			    $dstArr[$key]['conf'] = array_values($dstArr[$key]['conf']);
			    if(count($dstArr) > 1) {
				$dstArr = array_values($dstArr);
			    }
                            $conf[$type] = $dstArr;
                            $updateArr = array(
                                'sync_status' => 0,
                                'config'      => json_encode($conf),
                            );
                            $ret = $this->updateGroupCustom($groupId, $updateArr);
                            return $ret;
                        }
                    }
                }
            }
            return null;

        }
    }
            
    /**
    *   @desc   集群配置下发
    *   @param  集群id
    *   @return boolean
    */
    public function distGroupConf($id) {
        // 读取配置
        $daoObj = Dao_DaoFactory::getInstance('Dao_Group');
        $select = array('group_name', 'config');
        $where  = array(
            'id=' => $id,
        );   
        $selectInfo = $daoObj->doSelect($select, $where);
        if(NULL === $selectInfo || false === $selectInfo) {
            Tnoah_OutPutUtil::responseSqlError();                                    
        }
        $confInfo  = $selectInfo[0]['config'];
        $groupName = $selectInfo[0]['group_name'];
        
        // 配置不存在 && 配置不是json格式
        if(is_null(json_decode($confInfo))) {
            $retArr = array(
                'error_code' => 22001,
                'error_msg'  => 'Incorrect configuration format',
            );
            Tnoah_OutPutUtil::response($retArr);                             
        }

        // 配置文件下发分为以下几个步骤
        // 1.将配置内容写入本地文件 odp/data/app/tnoah/$groupName.conf
        $fName    = $groupName . '.conf';
        $dataPath = Tnoah_OutPutUtil::getDataPath();
        $fName    = $dataPath . $fName;
        $ret = file_put_contents($fName, $confInfo );
        if($ret <= 0) {
            $retArr = array(
                'error_code' => 22001,
                'error_msg'  => 'Distribute failed: configuration file generation failed',
            );
            Tnoah_OutPutUtil::response($retArr);       
        }
        // 调用 dao 层的方法获取该集群所包含的全部机器
        $daoObj = Dao_DaoFactory::getInstance('Dao_Machine');
        $select = array('ip_addr');
        $where  = array(
            'group_id=' => $id,
        );
        $selectInfo = $daoObj->doSelect($select, $where);
        if(NULL === $selectInfo || false === $selectInfo) {
            $retArr = array(
                'error_code' => 22001,
                'error_msg'  => 'Distribute failed: get the machine list failed',
            );
            Tnoah_OutPutUtil::response($retArr); 
        }
        $ipArr = array();
        foreach($selectInfo as $value) {
            $ipArr[] = $value['ip_addr'];
        }

        // 调用 分发服务 进行分发
        $rsyncObj = new Tnoah_RsyncUtil();
        $ips=implode(",",$ipArr);
	
	// $tagent_ver = '1.x';	
	$tagent_ver = '2.x';	
	if($tagent_ver == '1.x') {
            $file_dst_dir = "/home/work/tagent/conf/";  // 老版本
	}else {
            $file_dst_dir = "/usr/local/tagent/item_conf/";
	}
        $dst_user     = "work";
	$err_cmd      = "";
        $ret          =  $rsyncObj->execute($ips,$fName,$file_dst_dir,$dst_user, $err_cmd);
	
        if($ret === false) {
            $retArr = array(
                'error_code' => 22001,
                'error_msg'  => 'Rsync failed!',
		'err_cmd'    => $err_cmd,
            );
            Tnoah_OutPutUtil::response($retArr); 
        } 


        $conf = json_encode($confInfo);
        if(!$conf) {
            $conf = "";
        }
        // unlink($fName);  // 收尾工作1: 删除临时文件
        // 收尾工作2   更新 sync_status
        $updateArr = array(
            'sync_status' => 1,
//            'config'      => $conf,
        );
        $ret = $this->updateGroupCustom($id, $updateArr);
        return $ret;
    }

    /**
    *   @desc   获取集群下的配置
    *   @return array
    */
    public function getGroupConf($groupId) {
        $confArr = $this->_getGroupConf($groupId);
        $splitArr = $this->_splitConfig($confArr);
        return $splitArr;
    }

    /**
    *   合并日志类型的配置项
    *   @param1  $val     array  要合并的内容 
    *   @param2  $logArr  array  当前的日志数组 
    *   @return  $retArr  array  合并后的数组
    */
    private function _mergeLogArr($val, $logArr) {
        print_r($val);
        print_r($logArr);
        $log_path   = $val['log_path'];
        $log_style  = $val['log_style'];
        $log_format = $val['log_format'];
        // 当前路径存在
        foreach($logArr as $k => $v) {
            if($log_path == $v['log_path']) {

            }
        }
        // 当前路径不存在
        $newArr = array(
            'property' => array(),
            'conf'     => array(),
        );
        $newArr['property']['log_path']  = $log_path;
        $newArr['property']['log_style'] = $log_style;
        if(isset($log_format)) {
            $newArr['property']['log_format'] = $log_format;
        }
        foreach($val as $k => $v) {
            if(!in_array($k, array('type', 'log_path', 'log_style', 'log_format'))){
                $newArr['conf'][$k] = $v;
            }
        }
        $logArr[] = $newArr;
        return $logArr;
    }

    /**
    *   @desc  获取集群下配置
    *   @return array
    */
    private function _getGroupConf($groupId) {
        // 读取配置
        $daoObj = Dao_DaoFactory::getInstance('Dao_Group');
        $select = array('config');
        $where  = array(
            'id=' => $groupId,
        );   
        $selectInfo = $daoObj->doSelect($select, $where);
        
        if(NULL == $selectInfo || false == $selectInfo) {
            Tnoah_OutPutUtil::responseSqlError();                                    
        }
        return json_decode($selectInfo[0]['config'], true);
    }

    /**
    *   @desc  合并配置项(prot & proc类型的)  
    *   @return array
    */
    private function _mergeConfPnP($srcConf, $name, $val, $tag, $proc_deeply_path = "") {
        foreach($srcConf as $k => $v) {
            if($v['item_name_prefix'] == $name) {  // 重名, 覆盖
                if($tag == 'PROC') {
                    $srcConf[$k]['proc_path']  = $val;
                    $srcConf[$k]['proc_deeply_path'] = $proc_deeply_path;
                }else {
                    $srcConf[$k]['port_num'] = $val;                    
                }
                return $srcConf;
            }
        }
        
        // 不重名, 新增
        $newConf = null;
        if($tag == 'PROC') {
            $newConf = array(
                'proc_path'        => $val,
                'item_name_prefix' => $name,
                'proc_deeply_path' => $proc_deeply_path,
            );
            $srcConf[] = $newConf;
        }else {
            $newConf = array(
                'port_num'         => $val,
                'item_name_prefix' => $name,
            );
            $srcConf[] = $newConf;            
        }
        return $srcConf;
    }

    /**
    *   @desc  拆分配置项(log类型的)
    *   @return array / false
    */
    private function _splitConfig($confArr) {
        $splitArr = array();
        $logArr  = $confArr['log'];
        $portArr = $confArr['port'];
        $procArr = $confArr['proc'];
        // log
        foreach($logArr as $key => $val) {
            $property = $val['property'];
            $conf     = $val['conf'];
            foreach($conf as $key1 => $val1) {
                $curArr = array();
                $curArr['type']       = 'log';
                $curArr['log_path']   = $property['log_path'];
                $curArr['log_style']  = $property['log_style'];
                $curArr['log_format'] = $property['time_format'];
                foreach($val1 as $key2 => $val2) {
                    $curArr[$key2] = $val2;
                }
                $splitArr[] = $curArr;
            }
        }

        foreach($portArr as $key => $val) {
            $curArr = array();
            $curArr['type'] = 'port';
            foreach($val as $key1 => $val1) {
                $curArr[$key1] = $val1;    
            }
            $splitArr[] = $curArr;        
        }
        // proc
        foreach($procArr as $key => $val) {
            $curArr = array();
            $curArr['type'] = 'proc';
            foreach($val as $key1 => $val1) {
                $curArr[$key1] = $val1;        
            }
            $splitArr[] = $curArr;            
        }
        return $splitArr;
    }

    /**
    *   @desc  合并配置项(log类型的)
    *   @return array / false
    */  
    private function _mergeConfLog($srcConf, $name, $match_val, $log_path, $log_style, $log_format, $match_type, $filt_str = '') {
        foreach($srcConf as $key => $val) {
            if($val['property']['log_path'] == $log_path && $val['property']['log_style'] == $log_style) {
                $pathConf = $val['conf'];
                foreach($pathConf as $key1 => $val1) {
                    if($name == $val1['item_name_prefix']) {
                        if(!isset($val1[$match_type])) {
                            echo "error";
                            return false;
                        }
                        // merge (覆盖)
                        $srcConf[$key]['conf'][$key1]['item_name_prefix'] = $name;
                        $srcConf[$key]['conf'][$key1][$match_type] = $match_val;
			if($filt_str != '') {
                        	$srcConf[$key]['conf'][$key1]['filt_str'] = $filt_str;
			}
                        return $srcConf;
                    }
                }
                // 相同路径, 不同的名称
                // merge (路径下新增)
		if($filt_str != '') {
                	$srcConf[$key]['conf'][] = array(
                	    $match_type        => $match_val,
                	    'item_name_prefix' => $name,
			    'filt_str'         => $filt_str,
                	);
		}else {
                	$srcConf[$key]['conf'][] = array(
                	    $match_type        => $match_val,
                	    'item_name_prefix' => $name,
                	);
		}
                return $srcConf;
            }
        }
        // 不同的路径
        // merge (新增路径)
        if($log_style == 'split') {
	    if($filt_str != '') {
            	$srcConf[] = array(
            	    'property' => array(
            	        'log_path'  => $log_path,
            	        'log_style' => $log_style,
            	    ),
            	    'conf'     => array(
            	        array(
            	            $match_type        => $match_val,
            	            'item_name_prefix' => $name,
			    'filt_str'         => $filt_str,
            	        ),
            	    ),
            	);
	    }else {
            	$srcConf[] = array(
            	    'property' => array(
            	        'log_path'  => $log_path,
            	        'log_style' => $log_style,
            	    ),
            	    'conf'     => array(
            	        array(
            	            $match_type        => $match_val,
            	            'item_name_prefix' => $name,
            	        ),
            	    ),
            	);
	    }
        }else {
	    if($filt_str != '') {
                $srcConf[] = array(
                    'property' => array(
                        'log_path'    => $log_path,
                        'log_style'   => $log_style,
                        'time_format' => $log_format,
                    ),
                    'conf'     => array(
                        array(
                            $match_type        => $match_val,
                            'item_name_prefix' => $name,
			    'filt_str'         => $filt_str,
                        ),
                    ),
                );
	    }else {
                $srcConf[] = array(
                    'property' => array(
                        'log_path'    => $log_path,
                        'log_style'   => $log_style,
                        'time_format' => $log_format,
                    ),
                    'conf'     => array(
                        array(
                            $match_type        => $match_val,
                            'item_name_prefix' => $name,
                        ),
                    ),
                );
	    }
        }
        return $srcConf;
    }
    

}
