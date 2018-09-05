	<?php
/**
 * @name Action_Sample
 * @desc sample action, 和url对应
 * @author API Group
 */
class Action_Sample extends Ap_Action_Abstract {

	/**
	 *
	 * @param input 
	 * @return result 
	 *
	 **/
	public function execute() {
		//1. check if user is login as needed
		$arrUserinfo = Saf_SmartMain::getUserInfo();
		if (empty($arrUserinfo)) {
			//ouput error
		}

		//2. get and validate input params
		$arrRequest = Saf_SmartMain::getCgi();
		$arrInput = $arrRequest['get'];
		if(!isset($arrInput['id'])){
			//output error
		}
		Bd_Log::debug('request input', 0, $arrInput);

		//3. call PageService
		$objServicePageSample = new Service_Page_Sample();
		$arrPageInfo = $objServicePageSample->execute($arrInput);


		//4. chage data to out format
		$arrOutput = $arrPageInfo;

		//5. build page
		// smarty模板，以下渲染模板的代码依赖于提供一个tpl模板
		//$tpl = Bd_TplFactory::getInstance();
		//$tpl->assign($arrOutput);
		//$tpl->display('en/newapp/index.tpl');

		//这里直接输出,作为示例
		$strOut = $arrOutput['data'];
		echo $strOut;

		//notice日志信息打印，只需要添加日志信息，saf会自动打一条log
		Bd_Log::addNotice('out', $arrOutput);

	}

}
