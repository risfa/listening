<?php
	date_default_timezone_set("Asia/Jakarta");
	$file = file("/home/dapps/public_html/listening/indicators/doneFlag");
	if($file[0] == 1) {
		exec("ps ax | grep done.php",$res);
		foreach($res as $val) {
			if(preg_match("/php \/home\/dapps\/public_html\/listening\/done\.php/",$val)) {
				$tmp = explode("?",$val);
				break;
			}
		}
		exec("kill -9 ".$tmp[0]);
		file_put_contents("/home/dapps/public_html/listening/engineLog/killEngineDone",date("Y-m-d H:i:s") . "\tEngine done mati",FILE_APPEND);
		$flag = fopen("/home/dapps/public_html/listening/indicators/doneFlag","w");
		fwrite($flag,"0");
		fclose($flag);
	}
?>
