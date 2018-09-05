<?php
/**
*   机器CURD操作
*   @author zhangweizhi@taihe.com
*/
class Service_Machine {

    /**
    *   @desc   删除指定机器
    *   @return int/NULL
    */
    public function deleteMachine($id) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Machine');
        $where  = array(
            'id=' => $id,
        );
	// 获取group_id
	$select = array(
		'group_id',
		'ip_addr',
	);
	$selectInfos = $daoObj->doSelect($select, $where);
        $infos = $daoObj->doDelete($where);
	// 删除机器后, 清空机器上当前集群的配置文件
	if($selectInfos && $selectInfos[0] && $selectInfos[0]['group_id'] && $selectInfos[0]['ip_addr']) {
		$groupId = intval($selectInfos[0]['group_id']);
		$ipAddr  = $selectInfos[0]['ip_addr'];
     	        $ret     = $this->eraseMachineConf($ipAddr, $groupId);
	}
        return $infos;
    }

    /**
    *   @desc   增加机器
    *   @return int/NULL
    */
    public function addMachine($ipAddr, $name, $groupId) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Machine');
	// 根据group_id获取service_line_id
        $daoGroupObj = Dao_DaoFactory::getInstance('Dao_Group');
	$where = array(
		'id=' => $groupId,
	);
	$select = array('service_line_id');
	$infos = $daoGroupObj->doSelect($select, $where);
	$serviceLineId = 0;
	if($infos && $infos[0] && $infos[0]['service_line_id']) {
		$serviceLineId = intval($infos[0]['service_line_id']);
	}

        $insert = array(
            'ip_addr'                      => $ipAddr,
            'name'                         => $name,
            'group_id'                     => $groupId,
	    'service_line_id'              => $serviceLineId,
        );        
	
        $infos = $daoObj->doInsert($insert);
	// 下发当前集群的配置文件到新增的机器上
	if($infos) {
	    $ipAddr  = $ipAddr;
     	    $ret     = $this->setMachineConf($ipAddr, $groupId);
	}
        return $infos;
    }

    /**
    *   @desc   更新指定机器
    *   @return int/NULL
    */
    public function updateMachine($id, $ipAddr, $name, $groupId) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Machine');
        $update = array();
        if(isset($ipAddr)) {
            $update['ip_addr'] = $ipAddr;
        }
        if(isset($name)) {
            $update['name'] = $name;
        }
        if(isset($groupId)) {
            $update['group_id'] = $groupId;
        }    
        $where = array(
            'id=' => $id
        );
        // print_r($update);die;
        $infos = $daoObj->doUpdate($update, $where);
        return $infos;
    }

    /**
    *   @desc   查询指定集群下的机器
    *   @return array
    */
    public function getMachineInfos($groupId) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Machine');
		$select = array('*');
        $where  = array(
            'group_id=' => $groupId,
        );
        $infos = $daoObj->doSelect($select, $where);
	if($infos[0] && !$infos[0]['ip_addr'] && !$infos[0]['name']) {
		return false;
	}
        return $infos;
    }

    public function getMachineByName($machineName) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Machine');
	$select = array('*');
        $where  = array(
            'name=' => $machineName,
        );
        $infos = $daoObj->doSelect($select, $where);
        return $infos;     
    }

    /*
    *    @desc   获取Tnoah平台下全部机器列表
    *	 @return array()
    */

    public function getAllMachineInfo() {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Machine');
	$select = array('*');
        $where  = array('id >= 0');
	$infos  = $daoObj->doSelect($select, $where);
	return $infos;
    }

    /**
    *   @desc   清空机器下(摘除机器时调用)
    *   @return array
    */
    public function eraseMachineConf($ipAddr, $groupId) {
	// 获取集群的名称
	$daoObj = Dao_DaoFactory::getInstance('Dao_Group');
	$select = array('group_name');
	$where  = array(
		'id=' => $groupId,
	);
	$selectInfo = $daoObj->doSelect($select, $where);
	$groupName  = $selectInfo[0]['group_name'];
	if(!$groupName) {
	    $retArr = array(
                'error_code' => 22001,
                'error_msg'  => 'Incorrect configuration format',
            );
            Tnoah_OutPutUtil::response($retArr);  
	}
	// 下发步骤1: 将配置文件(空)写入本地文件:/odp/data/app/tnoah/empty
	$dataPath = Tnoah_OutPutUtil::getEmptyDataPath();	
	$fname     = $dataPath . $groupName . '.conf';
	$ret       = file_put_contents($fname, '');
	if($ret < 0) {
		$retArr = array(
			'error_code' => 22001,
			'error_msg'  => 'Distribute failed: configuration file generation failed',
		);
		Tnoah_OutPutUtil::response($retArr);
	}
	// 下发步骤2:调用分发服务进行分发
	$fileDstDir = '/usr/local/tagent/item_conf/';
	$errCmd     = '';
	$rsyncObj   = new Tnoah_RsyncUtil();
	$ret        = $rsyncObj->execute($ipAddr, $fname, $fileDstDir, 'work', $errCmd);

	if($ret === false) {
            $retArr = array(
                'error_code' => 22001,
                'error_msg'  => 'Rsync failed!',
		'err_cmd'    => $err_cmd,
            );
            Tnoah_OutPutUtil::response($retArr); 
        } 
	return true;
    }


    /**
    *   @desc   清空机器下(摘除机器时调用)
    *   @return array
    */
    public function setMachineConf($ipAddr, $groupId) {
	// 获取集群的名称
	$daoObj = Dao_DaoFactory::getInstance('Dao_Group');
	$select = array('group_name', 'config');
	$where  = array(
		'id=' => $groupId,
	);
	$selectInfo = $daoObj->doSelect($select, $where);
	$groupName  = $selectInfo[0]['group_name'];
        $confInfo  = $selectInfo[0]['config'];
	if(!$groupName) {
	    $retArr = array(
                'error_code' => 22001,
                'error_msg'  => 'Incorrect configuration format',
            );
            Tnoah_OutPutUtil::response($retArr);  
	}
        // 配置不存在 && 配置不是json格式
        if(is_null(json_decode($confInfo))) {
	    if($confInfo != '') {
                $retArr = array(
                    'error_code' => 22001,
                    'error_msg'  => 'Incorrect configuration format',
                );
                Tnoah_OutPutUtil::response($retArr);
	    }
        }

	// 下发步骤1: 将配置文件写入本地文件:/odp/data/app/tnoah/
	$dataPath = Tnoah_OutPutUtil::getDataPath();	
	$fname     = $dataPath . $groupName . '.conf';
	$ret       = file_put_contents($fname, $confInfo);
	if($ret < 0) {
		$retArr = array(
			'error_code' => 22001,
			'error_msg'  => 'Distribute failed: configuration file generation failed',
		);
		Tnoah_OutPutUtil::response($retArr);
	}
	// 下发步骤2:调用分发服务进行分发
	$fileDstDir = '/usr/local/tagent/item_conf/';
	$errCmd     = '';
	$rsyncObj   = new Tnoah_RsyncUtil();
	$ret        = $rsyncObj->execute($ipAddr, $fname, $fileDstDir, 'work', $errCmd);

	if($ret === false) {
            $retArr = array(
                'error_code' => 22001,
                'error_msg'  => 'Rsync failed!',
		'err_cmd'    => $errCmd,
            );
            Tnoah_OutPutUtil::response($retArr); 
        } 
	return true;
    }

    /* 
	从开放云获取全部机器列表
	@return  array()
    */

    public function getMachineListFromBCC() {
	// 生成签名(bj)
	$bccObj     = new Tnoah_BccUtil();
	$httpMethod = 'GET';
	$path       = '/v2/instance';	
	$host       = "bcc.bj.baidubce.com";
	$headers    = array(
		"Content-Type" => "application/json", 
		"Host"         => $host,
	);
	$params       = array(
	    "maxKeys" => "10000",
	);
	$options                  = array("headersToSign" => $headers);
	$authentation             = $bccObj->getAuthentication($httpMethod, $path, $headers, $params, $options);
	$headers["Authorization"] = $authentation;
	// 获取结果(bj)
	$url          = $host . $path . '?maxKeys=10000';
	$httpObj      = new Tnoah_HttpProxy();
	$ret          = $httpObj->requestBCC($url, $headers);
	$jsonRetBj    = json_decode($ret, 1)['instances'];

	// 生成签名(gz)
	$host                     = "bcc.gz.baidubce.com";
	$headers["Host"]          = $host;
	$authentation             = $bccObj->getAuthentication($httpMethod, $path, $headers, $params, $options);
	$headers["Authorization"] = $authentation;
	// 获取结果(gz)
	$url          = $host . $path . '?maxKeys=10000';
	$ret = $httpObj->requestBCC($url, $headers);
        $jsonRetGz = json_decode($ret, 1)['instances'];

	// Merge
	$retArr = array();
	foreach($jsonRetBj as $val) {
	    $retArr[] = $val;
        }
	foreach($jsonRetGz as $val) {
	    $retArr[] = $val;
        }
	return $retArr;
    }
}