<?php
final class Mobilyws extends SmsGate {
	private $baseurl = "http://www.mobily.ws/api/";
	
	public function send() {
		$api_id = $this->api_key;

		$params = array (
			'mobile' => $this->username,
			'password' => $this->password,
			'numbers' => $this->to,
			'sender' => $this->from,
			'msg' => $this->message,
			'applicationType' => 24,
			
		);
		
		if (!$this->validateLatin($this->message)) {
			$params['text'] = $this->hex_chars($this->message);
		}
		
		$ret = $this->request('msgSend.php', $params);
		
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
	
	private function hex_chars($data) {
		$mb_hex = '';
		
		for($i = 0; $i < mb_strlen($data, 'UTF-8'); ++$i){
			$c = mb_substr($data,$i,1,'UTF-8');
			$o = unpack('N',mb_convert_encoding($c,'UCS-4BE','UTF-8'));
			$mb_hex .= sprintf('%04X',$o[1]);
		}
		
		return $mb_hex;
	}
	
	private function validateLatin($string) {
		$result = false;
	 
		if (preg_match("/^[\w\d\s.,-]*$/", $string)) {
			$result = true;
		}
	 
		return $result;
	}
}
?>
