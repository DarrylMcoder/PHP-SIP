<?PHP
    
require('../PhpSIP.class.php');

$api = new \PhpSIP();
$api->setMethod("REGISTER");
$api->setURI("sip:sip.mcast.net");
$api->setFrom("sip:c2c@".$api->getSrcIp());
$api->setTo("sip:darryl@sip.onsip.com");
$api->setContact("sip:c2c@".$api->getSrcIp());
$api->addHeader("Expires: 86400");
echo $api->send();
    
?>
