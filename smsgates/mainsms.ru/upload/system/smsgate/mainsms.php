<?php
final class Mainsms extends SmsGate {
	private $baseurl = "http://mainsms.ru/api/mainsms/";
	
	public function send() {
		$params = array (
			'recipients' => $this->to,
			'sender' => $this->from,
			'message' => $this->message
		);
		
		if ($this->copy) {
			$params['recipients'] .= ',' . $this->copy;
		}
		
		$ret = $this->request('message/send', $params);
		
		return $ret;
	}
	
	private function request($method, $params) {
		$sign = $this->generateSign($params);
		$params = array_merge(array('project' => $this->username), $params);
		
		$url = $this->baseurl . $method . '?' . http_build_query(array_merge($params, array('sign' => $sign)), '', '&');
		
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
	
	private function generateSign($params)
	{
		$params['project'] = $this->username; 
	    ksort($params);
	    return md5(sha1(join(';', array_merge($params, Array($this->password))))); 
	}
}
?>
