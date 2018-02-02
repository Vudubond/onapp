<?php 
    
  
	require_once('auth.php');
	
	$logFile = "log_all_vm.log";
	if (file_exists($logFile)) { unlink ($logFile); }
	
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, "https://$URL/alerts.json");
	curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($curl, CURLOPT_USERPWD, "$username:$password");
    curl_setopt($curl, CURLOPT_HTTPHEADER, array("App-Key: $key"));
    $response = json_decode(curl_exec($curl),true);
	curl_close($curl);
	
	if (isset($response['error'])) {
        print "Error: " . $response['error']['errormessage'] . "\n";
        exit;
    }
	//
    foreach ($response as $array) {
		foreach ($array as $alerts['zombie_disks']) {
				print $alerts['zombie_disks'];		
				}
	}
?>
