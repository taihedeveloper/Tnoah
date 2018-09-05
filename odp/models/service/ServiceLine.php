<?php
/**
*   业务线CURD操作
*   该接口除了method参数和id参数，其他参数尽量均用post的形式进行传递(data)
*   @author zhangweizhi@taihe.com
*/
class Service_ServiceLine {

	/**
	*	@desc    获取全部业务线
	*	@return  int/NULL
	*/
	public function getAllServiceLine() {
		$daoObj = Dao_DaoFactory::getInstance('Dao_ServiceLine');
		$select = array('id', 'service_line_name');
		$infos  = $daoObj->doSelect($select);
		return $infos;
	}

	/**
	*	@desc	新增业务线
	*	@return int/NULL
	*/
	public function addServiceLine($serviceLineName) {
		$daoObj    = Dao_DaoFactory::getInstance('Dao_ServiceLine');
		$insertArr = array(
			'service_line_name' => $serviceLineName,
		);		
		$infos = $daoObj->doInsert($insertArr);
		return $infos;
	} 

	/**
	*	@desc	更新业务线
	*	@return int/NULL
	*/
	public function updateServiceLine($id, $serviceLineName) {
		$daoObj = Dao_DaoFactory::getInstance('Dao_ServiceLine');
		$update = array(
			'service_line_name' => $serviceLineName,
		);
		$where = array(
			'id=' => $id,
		);
		$infos = $daoObj->doUpdate($update, $where);
		return $infos;
	}

	/**
	*	@desc	删除业务线
	*	@return array
	*/
	public function deleteServiceLine($id) {
		$daoObj = Dao_DaoFactory::getInstance('Dao_ServiceLine');
		$delete = array(
			'id=' => $id,
		);
		$infos = $daoObj->doDelete($delete);
		return $infos;
	}

	/**
	*	@desc  查询指定业务线下的集群
	*	@return array
	*/

}