<?php
/**
 * @name Service_Page_SampleApi
 * @desc sample api page service
 * @author API Group
 */
class Service_Page_SampleApi {
	private $objServiceDataSample;
	public function __construct(){
		$this->objServiceDataSample = new Service_Data_Sample();
	}
	/**
	 *
	 * set注入
	 * @param property
	 * @param value
	 * @return 
	 *
	 **/
	public function __set($property,$value){
		$this->$property = $value;
	}

	/**
	 *
	 * @param input
	 * @return result
	 *
	 **/ 
	public function execute($arrInput){
		Bd_Log::debug('sample api page service  called');
		if($arrInput == null)
		{
			$arrCgi = Saf_SmartMain::getCgi();
			$arrInput = $arrCgi['get'];
		}
		$intId = intval($arrInput['id']);
		$arrResult = array();
		$arrResult['errno'] = 0;
		try{
			if($intId <= 0){
				$arrResult['errno'] = 222;//示例错误码
			}else{
				$strData = $this->objServiceDataSample->getSample($intId);
				$arrResult['data'] = $strData;
			}
		}catch(Exception $e){
			Bd_Log::warning($e->getMessage(), $e->getCode());
			$arrResult['errno'] = $e->getCode();
		}
		return $arrResult;
	}
}
