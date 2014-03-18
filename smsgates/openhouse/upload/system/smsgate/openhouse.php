<?php
final class openhouse extends SmsGate {
	private $baseurl = "http://api-openhouse.imimobile.com/smsmessaging/1/outbound/";
	
	public function send() {
		$ret = array();
		
    $params = array (
      'outboundSMSMessageRequest' => array (
        'address' => array ( 'tel:' . preg_replace("/[^0-9+]/", '', $this->to) ),
        'senderAddress' => 'tel:' . $this->from,
        'outboundSMSTextMessage' => array (
          'message' => $this->message
        ),
        'clientCorrelator' => '',
        'senderName' => ''
      )
    );
   
		if ($this->copy) {
			$numbers = explode(',', $this->copy);
			foreach ($numbers as $number) {
				$params['outboundSMSMessageRequest']['address'][] = 'tel:' . preg_replace("/[^0-9+]/", '', $number);
			}
		}
    
    $ret[] = $this->request('tel%3A%2B' . $this->from . '/requests', $params);
		
		return $ret;
	}
   
	private function request($method, $params) {
		$url = $this->baseurl . $method;
	
		$post = json_encode($params); 
    
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
      curl_setopt(
        $ch,
        CURLOPT_HTTPHEADER,
        array(
          'Content-Type: application/json',
          'key: ' . $this->password
        )
      );
			$buffer = curl_exec($ch);
			curl_close($ch);
		} else {
			$context = stream_context_create(array(
          'http' => array(
              'method' => 'POST',
              'header' => "Content-type: application/json\r\n" . 'key: ' . $this->password . "\r\n",
              'content' => $post,
              'timeout' => 10,
          ),
      ));
      $buffer = file_get_contents($url, false, $context);
		}
		
		return $buffer;
	}
}
?>