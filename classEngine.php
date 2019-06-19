<?php
	class Engine {
		protected $taskName;
		protected $dirDone;
		protected $engineFlag;
		protected $engineStart;
		protected $engineStop;
		protected $indicatorFolder;
		protected $url;
		protected $resultDir;
		protected $log;
		protected $RCX;
		
		public function setParam($taskName,$dirDone,$engineFlag,$engineStart,$engineStop,$indicatorFolder,$url,$resultDir,$log,$RCX = NULL) {
			$this->taskName = $taskName;
			$this->dirDone = $dirDone;
			$this->engineFlag = $engineFlag;
			$this->engineStart = $engineStart;
			$this->engineStop = $engineStop;
			$this->indicatorFolder = $indicatorFolder;
			$this->url = $url;
			$this->resultDir = $resultDir;
			$this->log = $log;
			$this->RCX = $RCX;
		}
		
		public function getParam() {
			return array(
										"taskName" => $this->taskName,
										"dirDone" => $this->dirDone,
										"engineFlag" => $this->engineFlag,
										"engineStart" => $this->engineStart,
										"engineStop" => $this->engineStop,
										"indicatorFolder" => $this->indicatorFolder,
										"url" => $this->url,
										"resultDir" => $this->resultDir
 									);
		}
		
		public function process($string) {
			$file = file($this->engineFlag);
			if($file[0] == "FALSE") {
				// rename($this->engineStop, $this->engineStart);
				$this->setTrueFlag();
				// $i = 0;
				$file = file($this->engineFlag);
				while($file[0] == "TRUE") {
					$handle = fopen($this->taskName,"r");
					if($handle) {
						while(($line = fgets($handle)) !== FALSE) {
							if($line == "" || empty($line)) {
								break;
							}
							$this->log(date("Y-m-d H:i:s")."\t".$string."\t".$line,"process_data");
							if(strtolower($string) == "worker") {
								$this->processWorker($line);
							} else if(strtolower($string) == "done"){
								$this->processDone($line);
							} else {
								$this->processReport($line);
							}
							usleep(1000000);
							$this->log(date("Y-m-d H:i:s")."\t".$string."\t".$line,"done_data");
							$this->removeFirstLine($this->taskName);
						}
						fclose($handle);
					} else {
						$this->log(date("Y-m-d H:i:s")."\terror open taskName ".$this->taskName."\n","error_open_file");
					}
					usleep(2000000);
					// if($i == 59) $this->setFalseFlag();
				}
			} else {
				// $this->log(date("Y-m-d H:i:s")."\talready running ".$this->taskName."\n","error_open_file");
				exit;
			}
		}
		
		protected function processReport($line) {
			$expData = explode("\t",$line);
			$dataProcess = $expData[0];
			$process = $expData[1];
			$timer = microtime(true);
			$explodeData = explode(";",$dataProcess);
			$explodeDate = explode(" - ",$explodeData[1]);
			$keyword = trim($explodeData[0]);
			$from = trim($explodeDate[0]);
			$to = trim($explodeDate[1]);
			$username = trim($explodeData[2]);
			$mode = trim($explodeData[3]);
			$data = array("keyword"=>$keyword,"from"=>$from,"to"=>$to,"username"=>$username,"mode"=>$mode,"data"=>$process);
			$res = $this->requestCURL($this->url,$data);
			$this->log(date("Y-m-d H:i:s")."\t".json_encode($res)."\n","report_curl");
		}
		
		protected function processDone($line) {
			if($line != "") {
				// sleep(60);
				$result = array();
				$explodeData = explode(";",$line);
				$explodeDate = explode(" - ",$explodeData[1]);
				$keyword = trim(strtolower($explodeData[0]));
				$logKeyword = trim(strtolower($explodeData[0]));
				$from = trim($explodeDate[0]);
				$from2 = trim($explodeDate[0]);
				$to = trim($explodeDate[1]);
				$mode = trim($explodeData[2]);
				$container = trim($explodeData[3]);
				$username = trim($explodeData[4]);
				$dateDiff =  date(strtotime($to) - strtotime($from));
				$diff = floor($dateDiff/(60*60*24))+1;
				if($mode == "submitAdvanced") $keywords = str_replace("+","%20",urlencode($keyword));
				else $keywords = str_replace("+","%20",urlencode($keyword));
				if($mode != "") {
					while(strtotime($from) <= strtotime($to)) {
						$reformatDate = explode("-",$from);
						$year = $reformatDate[0];
						$month = $reformatDate[1];
						$day = $reformatDate[2];
						$url = "http://5dapps.com/debi/result/".urlencode($mode)."/".$keywords."/".urlencode($container)."/".urlencode($year)."/".urlencode($month)."/".urlencode($day).".log";
						$httpResponse = $this->getHttpResponse($url);
						$this->log(date("Y-m-d H:i:s")."\t".$url."\t".$httpResponse."\n","http_response_code");
						$i = 1;
						if($httpResponse == 404) {
							usleep(1000000); // sleep 0.5 seconds, 1s = 1000000ms
							// setMicroTimeFile($indicatorFolder.$engineTimer);
							$i++;
							if($i == 30) {
								$this->setFalseFlag();
								$this->setDoneFlag();
								// throw new ErrorException("to long waiting ".$url);
								break;
							}
							// continue;
						} else {
							// $this->log(date("Y-m-d H:i:s")."\t".$url."\n","url_file_done");
							$data = $this->requestCURL($url);
							$this->joinData($data,$result,$from);
							$from = date("Y-m-d", strtotime("+1 day", strtotime($from)));
						}
					}
					if(preg_match("/@/",$keyword)) {
						$result["ER"] = round($result["ER"]/$diff,2);
					}
					$result["sentimen"]["goodwords"] = round($result["sentimen"]["goodwords"]/$diff,2);
					$result["sentimen"]["badwords"] = round($result["sentimen"]["badwords"]/$diff,2);
					$result["sentimen"]["netral"] = round($result["sentimen"]["netral"]/$diff,2);
					if(isset($result["tagcloud"])) arsort($result["tagcloud"]);
					$json = json_encode($result);
					// echo $json;die;
					// $this->log(date("Y-m-d H:i:s")."\t".$logKeyword."\t".$json."\n","done_file");
					$filename = $keyword.date("Ymd",strtotime($from2))."-".date("Ymd",strtotime($to)).$mode.".log";
					$this->doneFile($json,$filename);
				} else {
					$this->log(date("Y-m-d H:i:s")."\tEmpty mode","empty");
				}
			} else {
				$this->log(date("Y-m-d H:i:s")."\tEmpty String","empty");
			}
		}
		
		protected function processWorker($line) {
			$expData = explode("\t",$line);
			$dataProcess = $expData[0];
			$done = $expData[1];
			$explodeData = explode(";",$dataProcess);
			$explodeDate = explode(" - ",$explodeData[1]);
			$keyword = trim(strtolower($explodeData[0]));
			$from = trim($explodeDate[0]);
			$to = trim($explodeDate[1]);
			$mode = trim($explodeData[2]);
			$container = trim($explodeData[3]);
			$username = trim($explodeData[4]);
			while(strtotime($from) <= strtotime($to)) {
				$reformatDate = explode("-",$from);
				$year = $reformatDate[0];
				$month = $reformatDate[1];
				$day = $reformatDate[2];
				$checkFile = $this->resultDir.$mode."/".$keyword."/".$container."/".$year."/".$month."/".$day.".log";
				// if(!file_exists($checkFile)) {
					$result = array("keyword" => $keyword, "from" => $from, "mode" => $mode, "container" => $container, "username" => $username);
					$logProcess[] = $result;
					$options = [CURLOPT_FOLLOWLOCATION => false];
					$this->RCX->addRequest($this->url, $result, array($this, "callback_functn"), $user_data, $options, $headers);
				// }
				$from = date("Y-m-d", strtotime("+1 day", strtotime($from)));
			}
			$this->RCX->execute();
			$this->log(date("Y-m-d H:i:s")."\t".json_encode($logProcess)."\n","process_log_file");
			$this->doneFile($done);
			// return $done;
		}
		
		protected function joinData($json,&$result,$from) {
			$temp = json_decode($json,TRUE);
			$from = date("Y-m-d",strtotime($from));
			if(is_array($temp)) {
				foreach($temp as $key=>$val) {
					if(!isset($result[$key])) {
						$result[$key] = $val;
						if($key == "area") $result["medium"][$from] = $val;
						if($key == "ER") $result["ER_daily"][$from] = $val;
					} else {
						if($key == "area") {
							$result[$key] += $val;
							$result["medium"][$from] = $val;
						} else if($key == "unique_screenname") {
							$result[$key] += $val;
						} else if($key == "ER") {
							$result[$key] += $val;
							$result["ER_daily"][$from] = $val;
						} else if($key == "mention") {
							foreach($val as $k=>$v) {
								array_push($result[$key],$v);
							}
						} else if($key == "tagcloud") {
							if(empty($result[$key])) {
								$result[$key] = $val;
							} else {
								foreach($val as $k=>$v) {
									// foreach($v as $k1=>$v1) {
										if(array_key_exists($k,$result[$key])) {
											$result[$key][$k] += $v;
										} else {
											$result[$key][$k] = $v;
										}
									// }
								}
							}
						} else {
							foreach($val as $k=>$v) {
								if($k == "Iphone" || $k == "Android" || $k == "Web" || $k == "Blackberry" || $k == "Jakarta" || $k == "Depok" || $k == "Bogor" || $k == "Tangerang" || $k == "Bekasi" || $k == "goodwords" || $k == "badwords" || $k == "netral") {
									$result[$key][$k] += $v;
								}
							}
						}
						// print_r($result);
					}
				}
			}
		}
		
		protected function getHttpResponse($url) {
			$httpResponse = get_headers($url);
			$explodeHttp = explode(" ",$httpResponse[0]);
			return $explodeHttp[1];
		}
		
		protected function doneFile($data,$filename = NULL) {
			if($filename !== NULL) {
				file_put_contents($this->dirDone.$filename,$data);
			} else {
				file_put_contents($this->dirDone.$filename,$data,FILE_APPEND);
			}
			exec("chmod -R 0777 ".$this->dirDone.$filename);
			// if($filename !== NULL) exec("chmod 0777 ".$this->dirDone.$filename);
		}
		
		protected function setTrueFlag() {
			$flag = fopen($this->engineFlag,"w");
			fwrite($flag,"TRUE");
			fclose($flag);
		}
		
		protected function setDoneFlag() {
			$flag = fopen($this->indicatorFolder . "doneFlag","w");
			fwrite($flag,"1");
			fclose($flag);
		}
		
		protected function setFalseFlag() {
			$flag = fopen($this->engineFlag,"w");
			fwrite($flag,"FALSE");
			fclose($flag);
		}
		
		protected function log($message,$filename) {
			@error_log($message, 3,$this->log.$filename."_".date("ymd").".log");
		}
		
		protected function removeFirstLine($data) {
			$file = file($data);
			array_splice($file, 0, 1);
			file_put_contents($data,implode($file));
		}
		
		public function callback_functn($response, $url, $request_info, $user_data, $time) {
			$time."<br/><pre>"; //how long the request took in milliseconds (float)
			$request_info; //returned by curl_getinfo($ch)
			$this->log(date("Y-m-d H:i:s")."\t".json_encode($request_info)."\n","callback_curl");
			// file_put_contents("1",json_encode($request_info)."\n",FILE_APPEND);
		}
		
		protected function requestCURL($url,$data = array()) {
			// $data = array('postvar1' => 'value1')
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL,$url);
			curl_setopt($ch, CURLOPT_POST, 1);
			// in real life you should use something like:
			curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
			// receive server response ...
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			$result = curl_exec ($ch);
			curl_close ($ch);
			return $result;
		}
	}
?>