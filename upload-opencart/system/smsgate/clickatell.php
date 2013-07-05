<?php
/**
 * API Description: http://cdn.clickatell.com/downloads/http/Clickatell_HTTP.pdf
 */
final class Clickatell extends SmsGate {
	private $baseurl = "http://api.clickatell.com";
	
	public function send() {
		$api_id = $this->api_key;
				
		// do auth call
		$ret = $this->request('auth', array ('user' => $this->username, 'password' => $this->password, 'api_id' => $api_id));
		// explode our response. return string is on first line of the data returned
		
		$sess = explode(":", $ret);
		if ($sess[0] == "OK") {
			$sess_id = trim($sess[1]); // remove any whitespace
			
			$text = $this->message;
	
			$params = array (
				'session_id' => $sess_id,
				'to' => $this->to,
				'text' => $text,
				'from' => $this->from,
			);
			
			if (!$this->validateLatin($text)) {
				$params['unicode'] = 1;
				$params['text'] = $this->hex_chars($text);
			}
			
			$ret = $this->request('sendmsg', $params);
			$send = explode(":",$ret);
	 
			if ($send[0] == "ID") {
				return "successnmessage ID: ". $send[1];
			} else {
				return "send message failed";
			}
		} else {
			return "Authentication failure: ". $ret[0];
		}
	}
	
	private function request($method, $params) {
		$url = $this->baseurl . "/http/" . $method;
		
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
