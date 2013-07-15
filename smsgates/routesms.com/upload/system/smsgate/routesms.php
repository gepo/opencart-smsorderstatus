<?php
final class Routesms extends SmsGate {
	//private $baseurl = "http://smpp6.routesms.com:8080/bulksms/";
	private $baseurl = "http://121.241.252.114:8080/bulksms/";
	
	public function send() {
		$api_id = $this->api_key;
		
		$text = @iconv('UTF-8', 'ISO-8859-1', $this->message);

		$params = array (
			'username' => $this->username,
			'password' => $this->password,
			'type' => 5, // Plain text ISO-8859-1
			'dlr' => 0,  // No Delivery report required
			'destination' => $this->to,
			'source' => $this->from,
			'message' => $text,
		);
		
		if (strcmp($text, $this->message)) {
			$params['message'] = $this->hex_chars($this->message);
			$params['type'] = 2; // Unicode message
		}
		
		$ret = $this->request('bulksms', $params);
		
		return $ret;
	}
	
	private function request($method, $params) {
		$url = $this->baseurl . $method;
		
		$url .= '?' . http_build_query($params);
		
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
		//	curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
			$buffer = curl_exec($ch);
			curl_close($ch);
		} else {
			/*$context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $params,
                    'timeout' => 10,
                ),
            ));*/
            $buffer = file_get_contents($url);//, false, $context);
		}
		
		return $url . $buffer;
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
	
	private function sms_to_unicode($message) {
		$hex1 = '';
		$latin = @iconv('UTF-8', 'ISO-8859-1', $message);
		if (strcmp($latin, $message)) {
			return $this->hex_chars($message);
		} else {
			return $latin;
		}
	}
}
?>
