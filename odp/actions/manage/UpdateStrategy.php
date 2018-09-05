<?php
/**
*   更新制定策略id对应的策略信息
*   @author dupeng@taihe.com
*/
class Action_UpdateStrategy extends Ap_Action_Abstract {

    public function execute() {
        $arrRequest = Saf_SmartMain::getCgi();
        $param      = $arrRequest['request_param'];

        $id              = $param['id'];
        $note            = $param['note'];
        $metric          = $param['metric'];
        $func            = $param['func'];
        $op              = $param['op'];
        $right_value     = $param['right_value'];
        $max_step        = $param['max_step'];
        $priority        = $param['priority'];
        $run_begin       = $param['run_begin'];
        $run_end         = $param['run_end'];

        if(!isset($id) ) {
            Tnoah_OutPutUtil::responseParamError();                                    
        }
        $id            = intval($id); 
        if($id <= 0 ) {
            Tnoah_OutPutUtil::responseParamError();                                                
        }
        if(!isset($note) && !isset($metric) && !isset($func) && !isset($op) && !isset($right_value) && !isset($max_step) 
            && !isset($priority) &&!isset($run_begin) &&!isset($run_end)){
            Tnoah_OutPutUtil::responseParamError();
        }
        $strategyArr = array(
                'id' => $id,
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
        $strObj  = Service_LibFactory::getInstance('Service_Strategy');
        $infos = $strObj->updateStrategy($strategyArr);

        if(NULL === $infos) {
            Tnoah_OutPutUtil::responseSqlError();                        
        }
        $ret = array(
            'error_code' => 22000,
            'data'       => $infos,
        );
        Tnoah_OutPutUtil::response($ret);                
    }
}

             