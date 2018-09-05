<?php
/**
*   通过模板id添加策略配置
*   @author dupeng@taihe.com
*/
class Action_AddStrategy extends Ap_Action_Abstract {

    public function execute() {
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];

        $tpl_id          = $param['tpl_id'];
        $note            = $param['note'];
        $metric       = $param['metric'];
        $func         = $param['func'];
        $op = $param['op'];
        $right_value   = $param['right_value'];
        $max_step   = $param['max_step'];
        $priority   = $param['priority'];
        $run_begin  = $param['run_begin'];
        $run_end = $param['run_end'];

        if(!isset($tpl_id) || !isset($metric) || !isset($func) || !isset($op) || !isset($right_value) || !isset($max_step) || !isset($priority)) {
            Tnoah_OutPutUtil::responseParamError();                                    
        }
        $tpl_id = intval($tpl_id);
        if($tpl_id <= 0 ) {
            Tnoah_OutPutUtil::responseParamError();                                                
        }

        $strategyArr = array(
                'tpl_id' => $tpl_id,
                'note' => $note,
                'metric' => $metric,
                'func' => $func,
                'op' => $op,
                'right_value' => $right_value,
                'max_step' => $max_step,
                'priority' => $priority,
                'run_begin' => $run_begin,
                'run_end' => $run_end,
            );
        $strategyObj  = Service_LibFactory::getInstance('Service_Strategy');
        $infos     = $strategyObj->addStrategyByTid($strategyArr);

        if(NULL === $infos || false == $infos) {
            Tnoah_OutPutUtil::responseSqlError();                        
        }
        $ret = array(
            'error_code' => 22000,
            'data'       => $infos,
        );
        Tnoah_OutPutUtil::response($ret);                
    }
}