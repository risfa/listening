<?php
	date_default_timezone_set('Asia/Jakarta');
	require_once("classEngine.php");
	$engine = new Engine();
	$taskName = "/home/dapps/public_html/debi/task/done";
	$dirDone = "/home/dapps/public_html/debi/finalresult/";
	$engineFlag = "/home/dapps/public_html/listening/indicators/done.flag";
	$engineStart = "/home/dapps/public_html/listening/indicators/done.start";
	$engineStop = "/home/dapps/public_html/listening/indicators/done.stop";
	$indicatorFolder = "/home/dapps/public_html/listening/indicators/";
	$url = "http://5dapps.com/debi/result/";
	$resultDir = "/home/dapps/public_html/debi/result/";
	$log = "/home/dapps/public_html/listening/engineLog/";
	$string = "done";
	$engine->setParam($taskName,$dirDone,$engineFlag,$engineStart,$engineStop,$indicatorFolder,$url,$resultDir,$log);
	$engine->process($string);
?>