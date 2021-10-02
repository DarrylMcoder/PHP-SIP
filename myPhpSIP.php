<?PHP
    
class myPhpSIP extends PhpSIP{

  public $media_ip;
  public $media_port;
  public $media_type;
  
  protected function sendData($data){
    $r = parent::sendData($data);
    echo "\n[Offer]\n".$data."\n";
    return $r;
  }
  
  
  protected function readMessage($block = true)
  {
     if($block === false){
      socket_set_nonblock($this->socket);
    }else{
      socket_set_block($this->socket);
    }
    $from = "";
    $port = 0;
    $this->rx_msg = null;
    
    while($c = socket_recvfrom($this->socket, $this->rx_msg, 10000, 0, $from, $port) && socket_last_error($this->socket) === 4){
      if(!$c){
        $this->res_code = "No final response in ".round($this->fr_timer/1000,3)." seconds. (".socket_strerror(socket_last_error($this->socket)).")";
      return $this->res_code;
      }
    }
    
    if ($this->debug)
    {
      $temp = explode("\r\n",$this->rx_msg);
      
      echo "<-- ".$temp[0]."\n";
      echo "\n[Answer]\n".$this->rx_msg."\n";
    }
    
    // Response
    $m = array();
    if (preg_match('/^SIP\/2\.0 ([0-9]{3})/', $this->rx_msg, $m))
    {
      $this->res_code = trim($m[1]);
      
      $this->parseResponse();
    }
    // Request
    else
    {
      $this->parseRequest();
    }
    
    //parse body
    $this->parseBody();
    // is diablog establised?
    if (in_array(substr($this->res_code,0,1),array("1","2")) && $this->from_tag && $this->to_tag && $this->call_id)
    {
      if ($this->debug && !$this->dialog)
      {
        echo "  New dialog: ".$this->from_tag.'.'.$this->to_tag.'.'.$this->call_id."\n";
      }
      
      $this->dialog = $this->from_tag.'.'.$this->to_tag.'.'.$this->call_id;
    }
  }
  
  public function setMethod($method)
  {
    if (!in_array($method,$this->allowed_methods))
    {
      throw new PhpSIPException('Invalid method.');
    }
    
    $this->method = $method;
    
    if ($method == 'INVITE')
    {
      $body = "v=0\r\n";
      $body.= "o=click2dial 0 0 IN IP4 "."php-sip.herokuapp.com"."\r\n";
      $body.= "s=click2dial call\r\n";
      $body.= "c=IN IP4 "."php-sip.herokuapp.com"."\r\n";
      $body.= "t=0 0\r\n";
      $body.= "m=audio 8000 RTP/AVP 0 8 18 3 4 97 98\r\n";
      $body.= "a=rtpmap:0 PCMU/8000\r\n";
      $body.= "a=rtpmap:18 G729/8000\r\n";
      $body.= "a=rtpmap:97 ilbc/8000\r\n";
      $body.= "a=rtpmap:98 speex/8000\r\n";
      
      $this->body = $body;
      
      $this->setContentType(null);
    }
    
    if ($method == 'REFER')
    {
      $this->setBody('');
    }
    
    if ($method == 'CANCEL')
    {
      $this->setBody('');
      $this->setContentType(null);
    }
    
    if ($method == 'MESSAGE' && !$this->content_type)
    {
      $this->setContentType(null);
    }
  }
  
  public function was_recvd($method)
    { 
    if ($this->debug)
    {
      echo "Checking for ".$method."\n";
    }
        $this->readMessage(false); 
        
        if ($this->rx_msg && $this->req_method !== $method)
        {
          $this->reply(200,'OK');
          return false;
        }elseif($this->rx_msg && $this->req_method === $method){
           return $this->req_method;
         }else{
           return false;
         }
  }
  
  protected function parseBody(){
    $body = substr($this->rx_msg,strpos($this->rx_msg,"\r\n\r\n"));
    $body_array = explode("\r\n",$body);
    preg_match("#c\s?=\s?IN\s(IP[46])\s(.*?)\s#i",$body,$m);
    $this->media_ip_type = ($m[1] === "IP4") ? AF_INET : AF_INET6;
    $this->media_ip = $m[2];
    var_dump($m);
    preg_match("#m\s?=\s?(audio|video)\s([0-9]*)\s#i",$body,$m);
    $this->media_port = $m[2];
    $this->media_type = $m[1];
    var_dump($m);
    return true;
  }
  
}