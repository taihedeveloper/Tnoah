<?php
/*
*	Tnoah小时级数据同步脚本
*	执行间隔：每小时一次
*	@author zhangweizhi
*	
*/
require_once '/home/work/odp/app/tnoah/library/tnoah/SsdbUtil.php';
// ssdb地址
$par = array(
    'host' => '***.***.***.***',
    'port' => 'port',
);
$ssdb = new Tnoah_SsdbUtil($par);

$redis = new Redis();
$redis->connect('***.***.***.***', 9999);	// redis ip地址、端口号


// REDIS的数据
$keys_arr = $redis->keys('*');
foreach($keys_arr as $k => $v) {
	// 获取redis每个key的内容
	$redis_key = $v;
	$redis_arr = $redis->zrange($redis_key, 0, 500, true);
	// 写入ssdb
	$ssdb_key = $v;
	$ssdb_val = serialize($redis_arr);
	$ssdb->setx($ssdb_key, $ssdb_val, 3600*24*7);
	echo $ssdb_key . ' -- done' . "\n";
}

echo "done";die;
