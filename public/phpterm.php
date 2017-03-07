<?php 
$whitelist = array(dns_get_record('holly.kent.ac.uk', DNS_A)[0]['ip'], dns_get_record('hilly.kent.ac.uk', DNS_A)[0]['ip']);
if (php_sapi_name() == "cli" || in_array($_SERVER["REMOTE_ADDR"], $whitelist)){
        exec('/usr/bin/pkill -TERM php');
        http_response_code(200);
}
else{
        http_response_code(403);
}
exit;