<?php
/**
 * @name Main_Controller
 * @desc 主控制器,也是默认控制器
 * @author API Group
 */
class Controller_Main extends Ap_Controller_Abstract {
	//路由规则前面的不能大写
	public $actions = array(
		'sample' => 'actions/Sample.php',
		'sparksubmit'	 => 'actions/spark/SparkSubmit.php',
		'sparkstop'	 => 'actions/spark/SparkStop.php',
		'sparkstatus'	 => 'actions/spark/SparkStatus.php',
                'getsparktask'	 => 'actions/spark/GetSparkTask.php',
		'addsparktask'	 => 'actions/spark/AddSparkTask.php',
		'gethbasedata'	 => 'actions/hbase/GetHbaseData.php',
                'gethbasedata2'	 => 'actions/hbase/GetHbaseData2.php',
		'innerdata'	 => 'actions/hbase/InnerData.php',
		'ssdbmutiget'	 => 'actions/hbase/SsdbMutiGet.php',


		'rsyncutil'	 => 'actions/util/RsyncFile.php',
		
		'getallserviceline' => 'actions/manage/GetAllServiceLine.php',	// 获取全部业务线
		'addserviceline'    => 'actions/manage/AddServiceLine.php',	// 新增业务线
		'deleteserviceline' => 'actions/manage/DeleteServiceLine.php',	// 删除业务线
		'updateserviceline' => 'actions/manage/UpdateServiceLine.php',	// 更新业务线信息

		'getgroupinfos'   => 'actions/manage/GetGroupInfos.php',            // 获取指定业务线下的集群信息
		'addgroup'        => 'actions/manage/AddGroup.php',		    // 新增集群信息
		'deletegroup'     => 'actions/manage/DeleteGroup.php',	            // 删除集群信息
		'updategroup'     => 'actions/manage/UpdateGroup.php',	            // 更新集群信息
		'distgroupconf'   => 'actions/manage/DistGroupConf.php',            // 集群配置下发
		'groupinservline' => 'actions/manage/GroupInServline.php',          // 判断集群是否在指定业务线下
		'getgroupbyid'    => 'actions/manage/GetGroupById.php',	            // 获取集群信息
		'eraseconf'       => 'actions/manage/EraseConf.php',                // 删除机器的配置(摘除机器时使用)

		'getmachineinfos' => 'actions/manage/GetMachineInfos.php',	       // 获取指定集群下的机器
		'addmachine'      => 'actions/manage/AddMachine.php',		       // 新增机器
		'deletemachine'   => 'actions/manage/DeleteMachine.php',	       // 删除机器
		'machineingroup'  => 'actions/manage/MachineInGroup.php',              // 判断集群是否在指定业务线下
		'updatemachine'   => 'actions/manage/UpdateMachine.php',	       // 更新机器信息
		'getallmachineinfos' => 'actions/manage/GetAllMachineInfos.php',       // 获取所有集群下的机器

		'getmetrics'	  => 'actions/manage/GetMetrics.php',		       // 获取全部监控项
		'addmetric'	  => 'actions/manage/AddMetric.php',		       // 新增监控项
		'deletemetric'	  => 'actions/manage/DeleteMetric.php',		       // 删除监控项
		'updatemetric'	  => 'actions/manage/UpdateMetric.php',		       // 更新监控项信息
		

		'getprocmetrics'  	=> 'actions/manage/GetProcMetrics.php',	       // 获取进程监控项
		'getportmetrics'  	=> 'actions/manage/GetPortMetrics.php',	       // 获取端口监控项
		'getbussinessmetrics'   => 'actions/manage/GetBusinessMetrics.php',    // 获取进程监控项
		'getnaturemetrics' 	=> 'actions/manage/GetNatureMetrics.php',      // 获取性能监控项
		'getgroupmetricsname'   => 'actions/manage/GetGroupMetricsName.php',   // 获取集群下的全部监控项名称 (分类)

		'getgroupconf'		=> 'actions/manage/GetGroupConf.php',	       // 获取集群下配置
		'setgroupconf'        	=> 'actions/manage/SetGroupConf.php',	       // 改变集群下的配置
		'addgroupconf'        	=> 'actions/manage/AddGroupConf.php',	       // 增加集群下配置
		'deletegroupconf'     	=> 'actions/manage/DeleteGroupConf.php',       // 删除集群下配置


		'gettemplates'          => 'actions/manage/GetTemplates.php',          // 根据集群id获取所有模板信息
        	'addtemplate'           => 'actions/manage/AddTemplate.php',           // 往某个集群添加模板信息
        	'updatetemplate'        => 'actions/manage/UpdateTemplate.php',        // 更新某个模板信息
        	'deletetemplate'        => 'actions/manage/DeleteTemplate.php',        // 删除某个集群下的模板信息
        	'addstrategy'           => 'actions/manage/AddStrategyByTid.php',      // 向某个模板下添加策略
        	'getstrategy'           => 'actions/manage/GetStrategy.php',           // 通过策略id获取模板信息，用户更新模板信息使用
        	'updatestrategy'        => 'actions/manage/UpdateStrategy.php',        // 通过模板id更新模板信息
        	'deletestrategy'        => 'actions/manage/DeleteStrategy.php',        // 删除制定的策略（某个模板下的）
        	'getstrategylist'       => 'actions/manage/GetStrategyListByTid.php',  // 获取某个模板下的所有策略信息列表'

        	'geteventshow'          => 'actions/manage/GetEventShow.php',          // 获取到目前所有集群的报警情况
                'geteventipsshow'          => 'actions/manage/GetEventIpsShow.php',    // 获取到目前所有集群的报警机器的ip
                'getshieldendtimeshow'          => 'actions/manage/GetShieldEndTimeShow.php', //获取报警屏蔽结束时间
        	'updateeventstatus'     => 'actions/manage/UpdateEventStatus.php',     // 更新某个集群的状态
        	'gettopicname'          => 'actions/manage/GetTopicName.php',          // 更新某个集群的状态

        	'copystrategy'		=> 'actions/manage/CopyStrategy.php',          // 集群报警策略复制到其他集群工作（支持多个集群复制和全部复制）
        	'getmachinelistfrombcc'	=> 'actions/manage/GetMachineListFromBCC.php', // 从开放云获取全部机器列表
		'machinecmpfrombcc'     => 'actions/manage/MachineCmpFromBCC.php',     // 机器对照(Tnoah vs Bcc) 
	);
}
