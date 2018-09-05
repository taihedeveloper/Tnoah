<?php
/**
 * @name Service_Data_Sample
 * @desc sample data service, 按主题组织数据, 提供细粒度数据接口
 * @author API Group 
 */
class Service_Data_Sample {
	private $objDaoSample;
	public function __construct(){
		$this->objDaoSample = new Dao_Sample();
	}

	/**
	 *
	 * set注入
	 * @param property
	 * @param value
	 * @return true/false
	 *
	 **/
	public function __set($property,$value){
		$this->$property = $value;
	}   

	/**
	 *
	 * @param id 
	 * @return true/false
	 *
	 **/
	public function isExist($intId){
		if($intId > 0 && $intId < 100){
			return true;
		}
		return false;
	}

	/**
	 *
	 * @param input 
	 * @return result 
	 *
	 **/
	public function getSample($intId){
		Bd_Log::debug("sample data service getSample called");
		$strData =  $this->objDaoSample->getSampleById($intId);
		return $strData;
	}


	/**
	 *
	 * @param input 
	 * @return result 
	 *
	 **/
	public function addSample($strData){
		Bd_Log::debug("sample data service submitSample called");
		$arrFields = array('data'=>$strData);
		return $this->objDaoSample->addSample($arrFields);
	}

	/**
	 *
	 * @param input 
	 * @return result 
	 *
	 **/
	public function callOtherApp($intId){
		//跨子系统调用,这里调用自己作为示例
		$arrRet = Saf_Api_Server::call('tnoah','getSample', array('id' => $intId), null, null);
		if(false === $arrRet) {//异常逻辑处理       
			$arrErrorCodes = Saf_Api_Server::getLastError();
			$arrErrNo = array_keys($arrErrorCodes);
			$intErrNo  = $arrErrNo[0];
			$strErrMsg = $arrErrorCodes[$intErrNo];
			if($intErrNo == Saf_Api_Server:: METHOD_FAILED){
				$intErrNo = Saf_Api_Server:: getServiceError();
			}
			Bd_Log::warning($strErrMsg, $intErrNo, $arrParams);
			return false;
		}else{ //获取数据成功，正常逻辑处理           
			return $arrRet['data'];
		}
	}
}
