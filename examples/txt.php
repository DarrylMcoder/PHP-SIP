<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
 
<head><title>PHP-SIP Click to Call</title></head>
 
<body>
 
<?php if (isset($_POST['to'])) : ?>
 
  <?php require_once('..//PhpSIP.class.php') ?>
 
  <?php $to = $_POST['to'] ?>
  <?php $msg = $_POST['msg'] ?>
 
  Trying to send  <?php echo $msg ?> to <?php echo $to ?> ...<br />
 
  <?php flush() ?>
 
  <pre>
  <?php 
 
    try{
 
      $api = new PhpSIP();
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
      $api->setBody($msg);
      $api->setMethod('MESSAGE');
      $api->setFrom('sip:c2c@'.$api->getSrcIp());
      $api->setUri($to);
 
      $res = $api->send();
      echo "Body".$api->getBody();
 
      if ($res == 200) { 
        $api->listen('MESSAGE');
        $api->reply(200,'OK');
        $api->setMethod('MESSAGE');
        $api->addHeader('OK');
        $api->send();
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
 
  ?>
  </pre>
  <hr />
 
  <a href="<?php echo $_SERVER['PHP_SELF']; ?>">Back</a>
 
<?php else : ?>
 
  <form action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
    <fieldset>
      To: <input type="text" name="to" size="25" value="sip:enum-test@sip.nemox.net" />
      <input type="text" name="msg" >
      <input type="submit" value="Send" />
    </fieldset>
  </form>
 
<?php endif ?>
 
</body>
</html>