<?php
	$file = "indicators/*.start";
	$queue = glob($file);
	$i = 0;
	while($i<59) {
		usleep(1000000);
		$i++;
		if($i == 58) {
			foreach($queue as $val) {
				$newname = str_replace(".start",".stop",$val);
				// $flag = file($val);
				rename($val,$newname);
				// if($flag == "FALSE"){
				// }
			}			
		}
	}
?>