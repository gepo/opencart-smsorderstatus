<?php
final class Bytehand extends SmsGate {
	private $baseurl = "http://bytehand.com:3800/";
	
	public function send() {
		$ret = array();
		
		$params = array(
			'id'	=> $this->username,
			'key'	=> $this->password,
			'from'	=> $this->from,
			'to'	=> $this->to,
			'text'	=> $this->message
		);

		$ret[] = $this->request('send', $params);
		
		if ($this->copy) {
			$numbers = explode(',', $this->copy);
			foreach ($numbers as $number) {
				$params['to']     = $number;

				$ret[] = $this->request('send', $params);
			}
		}
		
		return $ret;
	}
   
	private function request($method, $params) {
		$url = $this->baseurl . $method;
		
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			$buffer = curl_exec($ch);
			curl_close($ch);
		} else {
			$context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $params,
                    'timeout' => 10,
                ),
            ));
            $buffer = file_get_contents($url, false, $context);
		}
		
		return $buffer;
	}
}
?>