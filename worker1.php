<?php
	date_default_timezone_set('Asia/Jakarta');
	require_once("classEngine.php");
	require_once("/home/dapps/public_html/debi/lib/RollingCurlX-master/rollingcurlx.class.php");
	$engine = new Engine();
	$RCX = new RollingCurlX(30);
	$taskName = "/home/dapps/public_html/debi/task/worker1";
	$dirDone = "/home/dapps/public_html/debi/task/done";
	$engineFlag = "/home/dapps/public_html/listening/indicators/worker1.flag";
	$engineStart = "/home/dapps/public_html/listening/indicators/worker1.start";
	$engineStop = "/home/dapps/public_html/listening/indicators/worker1.stop";
	$indicatorFolder = "/home/dapps/public_html/listening/lib/indicators/";
	$url = "http://5dapps.com/debi/api.php";
	$resultDir = "/home/dapps/public_html/debi/result/";
	$log = "/home/dapps/public_html/listening/engineLog/";
	$string = "worker";
	$engine->setParam($taskName,$dirDone,$engineFlag,$engineStart,$engineStop,$indicatorFolder,$url,$resultDir,$log,$RCX);
	$engine->process($string);
?>