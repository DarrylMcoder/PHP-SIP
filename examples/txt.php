<?php 
 
  require('..//PhpSIP.class.php');
  require('../myPhpSIP.php');
  
    try{
 
      $api = new myPhpSIP();
      // if you get "Failed to obtain IP address to bind. Please set bind address manualy."
      // error, use the line below instead
      // $api = new PhpSIP('you_server_IP_address');
 
      $api->setDebug(true);
 
      // if your SIP service doesn't accept anonymous inbound calls uncomment two lines below
      //$api->setUsername('auth_username');
      //$api->setPassword('auth_password');
 
      $api->addHeader('Content-Encoding: text/plain');
      $api->addHeader('Content-Disposition: alert');
      $api->addHeader('Content-Language: en');
      $api->setFrom('sip:c2c@php-sip.herokuapp.com');
      $api->setBody("Just testing");
      $api->setMethod('MESSAGE');
      //$api->setFrom('sip:c2c@'.$api->getSrcIp());
      $api->setUri("sip:+15195899829@sip.linphone.org");
 
      $res = $api->send();
 
      if ($res == 200) { 
        $api->listen('MESSAGE');
        $api->reply(200,'OK');
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