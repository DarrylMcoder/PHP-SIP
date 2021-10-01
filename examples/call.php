<?PHP

ini_set('error_reporting', E_ALL ^ E_NOTICE); 
ini_set('display_errors', 1); 
    
require("../PhpSIP.class.php");
require("../myPhpSIP.php");

// Set time limit to indefinite execution 
set_time_limit (0); 

$file = file_get_contents("http://ytapp.darrylmcoder.epizy.com/output.mp4");

// Set the ip and port we will listen on 
$address = isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : gethostbynamel(php_uname('n'))[0]; 
$port = 8000; 

// Create a TCP Stream socket 
$sock = socket_create(AF_INET, SOCK_STREAM, 0); 

// Bind the socket to an address/port 
socket_bind($sock, $address, $port) or die('Could not bind to address'); 

// Start listening for connections 
socket_listen($sock); 

// Non block socket type 
socket_set_nonblock($sock); 

try{
 
      $api = new myPhpSIP();
      // if you get "Failed to obtain IP address to bind. Please set bind address manualy."
      // error, use the line below instead
      // $api = new PhpSIP('you_server_IP_address');
 
      $api->setDebug(true);
 
      // if your SIP service doesn't accept anonymous inbound calls uncomment two lines below
      //$api->setUsername('auth_username');
      //$api->setPassword('auth_password');
 
      $api->addHeader('Subject: click2call');
      $api->setMethod('INVITE');
      $api->setFrom('sip:c2c@'.$api->getSrcIp());
      $api->setUri('sip:+15195899829@sip.linphone.org');
 
      $res = $api->send();
 
      if ($res == 200) {
        $ip_type = $api->media_ip_type;
        if(!$out_sock = @socket_create($ip_type, SOCK_DGRAM, SOL_UDP)){
          die("could not create socket ".socket_strerror(socket_last_error( $out_sock)));
        }
        socket_bind($out_sock,$api->get_src_ip());
        socket_connect($out_sock,$api->media_ip,$api->media_port);
        socket_write($out_sock,$file);
        socket_close($out_sock);
        // Loop continuously 
        while (!$api->was_recvd('BYE')) {
          
        }
      }
 
      if ($res == 'No final response in 5 seconds.') {
        $api->setMethod('CANCEL');
        $res = $api->send();
      }
 
      echo $res;
 
    } catch (Exception $e) {
 
      echo "Opps... Caught exception:";
      echo $e;
    }

// Close the master sockets 
socket_close($sock); 