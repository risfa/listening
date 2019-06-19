<?php
	date_default_timezone_set('Asia/Jakarta');
	require_once("classEngine.php");
	$engine = new Engine();
	$taskName = "/home/dapps/public_html/debi/task/report";
	$dirDone = "";
	$engineFlag = "/home/dapps/public_html/listening/indicators/report.flag";
	$engineStart = "/home/dapps/public_html/listening/indicators/report.start";
	$engineStop = "/home/dapps/public_html/listening/indicators/report.stop";
	$indicatorFolder = "/home/dapps/public_html/listening/lib/indicators/";
	$url = "http://5dapps.com/debi/create_report.php";
	$resultDir = "/home/dapps/public_html/debi/result/";
	$log = "/home/dapps/public_html/listening/engineLog/";
	$string = "report";
	$engine->setParam($taskName,$dirDone,$engineFlag,$engineStart,$engineStop,$indicatorFolder,$url,$resultDir,$log);
	$engine->process($string);
?>