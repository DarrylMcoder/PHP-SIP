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
        

// Loop continuously 
while (true) 
{ 
  
    unset($read); 

    $j = 0; 
  if(isset($client)){
    if (count($client)) 
    {
        foreach ($client as $k => $v) 
        {
            $read[$j] = $v; 

            $j++; 
        } 
    } 
  }

    $client = isset($read) ? $read : array(); 

    if ($newsock = @socket_accept($sock)) 
    { 
        if (is_resource($newsock)) 
        { 
            socket_write($newsock, $file );
            
            echo "New client connected $j"; 

            $client[$j] = $newsock;

            $j++; 
        } 
    } 
  if(isset($client)){
    if (count($client)) 
    {
        foreach ($client as $k => $v) 
        { 
            if (@socket_recv($v, $string, 1024, MSG_DONTWAIT) === 0)
            { 
                unset($client[$k]);

                socket_close($v); 
            } 
            else 
            { 
                if ($string) 
                { 
                    echo "$k: $string\n"; 
                } 
            } 
        } 
    } 
  }
  
  if($api->was_recvd("BYE")){
    $api->reply(200,"OK");
    break;
  }

    echo ".\r\n"; 
    ob_flush();
    flush();
    sleep(1); 
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