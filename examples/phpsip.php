<?PHP
    
require_once('../PhpSIP.class.php');

try {
  $api = new PhpSIP();/* IP we will bind to*/  
  $api->setMethod('MESSAGE');  
  $api->setFrom('sip:darryl@darrylmcoder.onsip.com');
  $api->setUri('sip:+1-519-589-9829');
  $api->setBody('Hi, can we meet at 5pm   today?');
  
  $res = $api->send(); echo "res1: $res\n";

} catch (Exception $e) {

  echo $e->getMessage()."\n";
}

?> 