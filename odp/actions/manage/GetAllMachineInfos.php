<?php
/**
*   获取所有集群下机器的信息
*   @author dupeng@taihe.com
*/
class Action_GetAllMachineInfos extends Ap_Action_Abstract {

    public function execute() {
        
        $serviceLineObj  = Service_LibFactory::getInstance('Service_ServiceLine');
        $allLines = $serviceLineObj->getAllServiceLine();
        $groupObj  = Service_LibFactory::getInstance('Service_Group');
        $mechObj  = Service_LibFactory::getInstance('Service_Machine');

        $allMatchineArray = array(); 
        if($allLines){
            foreach ($allLines as $key => $value) {
                $lineid = $value["id"];
                $productName = $value['service_line_name'];
                $ginfos = $groupObj->getGroupInfos($lineid);

                if(NULL === $ginfos || false == $ginfos) {
                    continue;                    
                 }
                $groupAll = array();
                foreach ($ginfos as $key => $value) {
                    $groupId = $value["id"];
                    $groupName = $value["group_name"];
                    $minfos = $mechObj->getMachineInfos($groupId);
                    if(NULL === $minfos || false == $minfos) {
                        continue;                       
                    }
                    $matchineTmpAll = array();
                    foreach ($minfos as $key => $value) {
                        $mid = $value['id'];
                        $ip_addr = $value["ip_addr"];
                        $name = $value["name"];
                        $matchineTmp = array(
                            "id" => $mid,
                            "ip_addr" => $ip_addr,
                            "name" => $name,
                            );
                        $matchineTmpAll[] = $matchineTmp;
                    }
                    $groupTmp = array(
                        'id' => $groupId , 
                        'group_name' => $groupName,
                        'mechines' => $matchineTmpAll
                        );
                    $groupAll[] =$groupTmp;
                 
                }
                $productTmp = array(
                        'id' => $lineid,
                        'service_line_name' => $productName,
                        'group' => $groupAll,
                        );
                $allMatchineArray[] = $productTmp;
            }
            
        }

        $ret = array(
            'error_code' => 22000,
            'data'       => $allMatchineArray,
        );
        Tnoah_OutPutUtil::response($ret);                
    }
}
