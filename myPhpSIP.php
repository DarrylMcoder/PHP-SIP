<?PHP
    
class myPhpSIP extends PhpSIP{
  
  
  protected function readMessage($block)
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
  
}