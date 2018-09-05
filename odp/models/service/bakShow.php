<?php
/**
*   报警仪表盘CURD操作
*   @author dupeng@taihe.com
*/
class Service_Show {

    /**
    *   @desc   更新指定监控项状态
    *   @return int/NULL
    */
    public function updateShow($show_id,$status,$start_time,$end_time,$shield_type) {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Show');
        $update = array(
            'show_status'         => $status,
        );         
        $where = array(
            'show_id =' => $show_id, 
         );
        $infos = $daoObj->doUpdate($update, $where);

        $shieldDaoObj = Dao_DaoFactory::getInstance('Dao_Shield');
        if($status=="1"){
            $insert = array(
                'shield_type'  => $shield_type,
                'value'   => $show_id,
                'start_time' => $start_time,
                'end_time'   => $end_time,
            );
            $infos = $shieldDaoObj->doInsert($insert);
        }else if($status=="0"){
            $where  = array(
                'value=' => $show_id,
            );
            $infos = $shieldDaoObj->doDelete($where);
        }
        return $infos;
    }

    /**
    *   @desc   查询集群所有监控项
    *   @return array
    */
    public function getEventShowInfos() {
        $daoObj = Dao_DaoFactory::getInstance('Dao_Show');
		$select = array('*');
        $where = array(
            'wrong_num !=' => 0, 
            '`show_status` IN (0, 1)',
        );
	$appends = array(
	   'ORDER BY `start_time` desc',
	);
        $infos = $daoObj->doSelect($select,$where,null,$appends);
        $infosTotal = array();
        foreach ($infos as $key => &$value) {
            $wrong_num = $value["wrong_num"];
            $total_num = $value["total_num"];
            if($total_num !=0 && $total_num != '0'){
                $value['rate'] = round($wrong_num/$total_num,4) *100 ."%";
            }else{
                $value['rate'] = '0%';
            }
            
            array_push($infosTotal, $value);
        }
        return $infosTotal;
    }

    
}
