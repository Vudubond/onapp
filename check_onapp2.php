<?php

$error = 0;
$warning = 0;
$errorstring = "CRITICAL:";
$warningstring = "WARNING:";

$options = getopt("H:n:u:p:w:c:i:o:");
if (!is_array($options)) {
    print "There was a problem reading in the options.\n\n";

    exit(1);
}
if (count($options) < 8) {
    $exampleUsage = <<< EXAMPLE
This script requires 8 arguments for execution:
 -H - API hostname (Control panel Name)
 -n - Node hostname to check
 -u - API username
 -p - API password
 -w - Warning server load value
 -c - Critical server load value
 -i - Limit of incoming 
 -o - Limit of outgoing q

An example call should be looking like:
php {$argv[0]} -H api.host.name -n node.host.name -u admin -p *pass* -w 3 -c 10 -i 500 -o 1000

EXAMPLE;

    print "Not all arguments are set.\n{$exampleUsage}";

    exit(2);
}

$apiurl = "https://" . $options['H'] . "/cgi-bin/api?call=api_get_json_server_status";
$userpwd = $options['u'] . ":" . $options['p'];
$curl = curl_init($apiurl);

curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
curl_setopt($curl, CURLOPT_USERPWD, $userpwd);
curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($curl, CURLOPT_USERAGENT, 'Nagios check');

$response = curl_exec($curl);
$resultStatus = curl_getinfo($curl);

if ($resultStatus['http_code'] == 200) {
    $status = json_decode($response, true);

    if (isset($status[$options['n']]) && is_array($status[$options['n']])) {
        $load1 = $status[$options['n']]['load one minute'];
        $load5 = $status[$options['n']]['load five minutes'];
        $incomingqueue = $status[$options['n']]['queue count delivery'];
        $outgoingqueue = $status[$options['n']]['queue count submission'];

        // check the warning and critical limits
        if ($load1 > $options['w']) {
            $warningstring .= " load (" . $load1 . ") higher than limit";
            $warning = 1;
        }

        if ($load1 > $options['c']) {
            $errorstring .= " load (" . $load1 . ") higher than limit";
            $error = 1;
        }

        if ($incomingqueue > $options['i']) {
            $errorstring .= " incoming queue (" . $incomingqueue . ") bigger than limit";
            $error = 1;
        }

        if ($outgoingqueue > $options['o']) {
            $errorstring .= " outgoing queue (" . $outgoingqueue . ") bigger than limit";
            $error = 1;
        }

        if ($error) {
            print $errorstring . "\n";
            exit(2);
        } elseif ($warning) {
            print $warningstring . "\n";

            exit(1);
        } else {
            $okstring = "OK: load5: " . $load5 . " ";
            $okstring .= "outgoing queue: " . $outgoingqueue . " ";
            $okstring .= "incomingqueue: " . $incomingqueue . "\n";
            print $okstring;

            exit(0);
        }
    }
} else {
    echo 'Call Failed ' . print_r($resultStatus, true);

    exit(2);
} // end http status check

exit(0);
