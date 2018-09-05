<?php
/**
 * 发送短信通知
 * @author duxianzhuan
 */

include_once(dirname(__FILE__).'/DBProxy.class.php');
include_once(dirname(__FILE__).'/HttpProxy.class.php');

$configDB = array(
                'hosts' => array('***.***.***.***'),    // 数据库ip
                'port' => 3306,   // 数据库端口
                'database' => 'tnoah',
                'username' => 'name',
                'password' => '12345qwert',
                'timeout' => 1,
);

//数据库初始化
$db = new DBProxy($configDB);

$sql = "select id from tnoah_group";

$ret = $db->queryAllRows($sql);
foreach($ret as $key => $val) {
	$url = 'http://127.0.0.1:8080/tnoah/distgroupconf?id=' . $val['id'];
	$res = HttpProxy::request($url, array(), 'GET', false, 3 , 10);
	$res = json_decode($res, 1);
	echo $val['id'] . "\t" . $res['error_code'] . "\n";
}

echo "done";

?>
