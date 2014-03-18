<?php
final class Infobip extends SmsGate {
	private $baseurl = "http://api.infobip.com/api/v3/";
	
	public function send() {
		$ret = array();
		
		$params = array(
			'user'	=> $this->username,
			'password'	=> $this->password,
			'sender'	=> $this->from,
			'to'	=> $this->to,
			'text'	=> $this->message
		);

		$params['to'] = preg_replace("/[^0-9+]/", '', $params['to']);
		
		if ($this->copy) {
			$params['to'] = array($params['to']);
			
			$numbers = explode(',', $this->copy);
			foreach ($numbers as $number) {
				$number = preg_replace("/[^0-9+]/", '', $number);
				
				$params['to'][] = $number;
			}
		} 
		
		$ret[] = $this->request('sendsms/xml', $params);
				
		return $ret;
	}
   
	private function request($method, $params) {
		$url = $this->baseurl . $method;
		
		$post = '<authentication>';
		$post .= '<username>' . $params['user'] . '</username>';
		$post .= '<password>' . $params['password'] . '</password>';
		$post .= '</authentication>';
		
		$post .= '<message>';
		$post .= '<sender>' . $params['sender'] . '</sender>';
		$post .= '<text>' . $params['text'] . '</text>';
		$post .= '<DataCoding>8</DataCoding>';
		$post .= '<recipients>';
		
		if (is_array($params['to'])) {
			foreach ($params['to'] as $number) {
				$post .= '<gsm>' . $number . '</gsm>';
			}
		} else {
			$post .= '<gsm>' . $params['to'] . '</gsm>';
		}
        
		$post .= '</recipients></message></SMS>';

		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-encoding: UTF-8"));
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			$buffer = curl_exec($ch);
			curl_close($ch);
		} else {
			$context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n" . 
								"Content-encoding: UTF-8\r\n",
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