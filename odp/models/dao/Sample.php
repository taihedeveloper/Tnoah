<?php
/**
 * @name Dao_Sample
 * @desc sample dao, 可以访问数据库，文件，其它系统等
 * @author API Group
 */
class Dao_Sample {
	const TABLE = 'tblSample';
	public function __construct(){
	}   

	/**
	 *
	 * @param input 
	 * @return result 
	 *
	 **/
	public function getSampleById($intId, $arrFields = null){
		return 'GoodBye World!';
	}

	/**
	 *
	 * @param input 
	 * @return result 
	 *
	 **/
	public function addSample($arrFields)
	{
		return true;
	}

	/**
	 *
	 * @param input 
	 * @return result 
	 *
	 **/
	public function updateSampleById($intId, $arrFields)
	{
		return true;
	}

	/**
	 *
	 * @param input 
	 * @return result 
	 *
	 **/
	public function deleteSampleById($intId)
	{
		return true;
	}

	/**
	 *
	 * @param input 
	 * @return result 
	 *
	 **/
	public function getSampleListByConds($arrConds, $arrFields, $intLimit, $intOffset, $arrOrderBy)
	{
		return true;
	}
}
