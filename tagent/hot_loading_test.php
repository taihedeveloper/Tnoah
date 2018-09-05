<?php

	$count = 1;
	while(1) {
		sleep(1);
		echo $count++;
		echo "\n";
		exec("cp /home/work/tagent/conf/example/lavaradio-group*.conf /home/work/tagent/conf");
	}
