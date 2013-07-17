<?php
final class Comtube extends SmsGate {
	private $baseurl = "http://api.comtube.ru/scripts/api/sms.php";
	
	public function send() {
		$api_id = $this->api_key;

		$params = array (
			'username' => $this->username,
			'number' => $this->to,
			'senderid' => $this->from,
			'message' => $this->message
		);
		
		$ret = $this->request('send', $params);
		
		return $ret;
	}
	
	private function request($method, $params) {
		$params['action'] = $method;
		$params['type'] = 'json';
		
		$url = $this->baseurl . '?' . $this->buildUrlWithSignature($params, $this->password);
		
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			$buffer = curl_exec($ch);
			curl_close($ch);
		} else {
            $buffer = file_get_contents($url);
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
	
	private function buildUrlWithSignature($params, $password)
	{
		if (is_array($params)) {
			ksort($params);
			$url = '';
			
			foreach($params as $key => $value) {
				$url .= $key . "=" . urlencode($value) . "&";
			}
			
			$signature = md5($url . '&password=' . urlencode($password));
			
			$url .= "signature=" . $signature;
			
			return $url;
		} else {
			return $url;
		}
	}
}
?>
