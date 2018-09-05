<?php
/**
 * @name Service_Page_Sample
 * @desc sample page service, 和action对应，组织页面逻辑，组合调用data service
 * @author API Group
 */
class Service_Page_Sample {
	private $objServiceDataSample;
	public function __construct(){
		$this->objServiceDataSample = new Service_Data_Sample();
	}
	/**
	 * set注入
	 * @param property
	 * @param value 
	 * @return null
	 *
	 **/
	public function __set($property,$value){
		$this->$property = $value;
	}

	/**
	 *
	 * @param array input
	 * @return array result
	 **/ 
	public function execute($arrInput){
		Bd_Log::debug('sample page service called');
		$arrResult = array();
		$arrResult['errno'] = 0;
		try{
			$intId = intval($arrInput['id']);
			if($intId <= 0){
				//参数错误的时候，从配置文件取消息
				$strData = Bd_Conf::getAppConf('sample/msg');
				$arrResult['data'] = $strData; 
			}else if($this->objServiceDataSample->isExist($intId)){
				//以下获取数据的方式提供3种示例，3选1
				//1. 调用本地DS
				$strData = $this->objServiceDataSample->getSample($intId);
				//2. 子系统交互, 注意：请确保conf/saf.conf中的api_lib配置成Tnoah, 否则会出错
				//$strData = $this->objServiceDataSample->callOtherApp($intId);
				//3. 调用本地库
				//$objUtil = new App2_Util();
				//$strData = $objUtil->getUtilMsg();
				$arrResult['data'] = $strData;
			}else{
				$arrResult['errno'] = 222;//示例错误码
			}
		}catch(Exception $e){
			Bd_Log::warning($e->getMessage(), $e->getCode());
			$arrResult['errno'] = $e->getCode();
		}
		return $arrResult;
	}
}
