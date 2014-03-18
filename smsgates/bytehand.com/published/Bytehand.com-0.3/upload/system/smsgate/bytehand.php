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

		$params['to'] = preg_replace("/[^0-9+]/", '', $params['to']);
		
		if ($this->copy) {
			$method = 'send_multi?id=' . $params['id'] . '&key=' . $params['key'];
			unset($params['id']);
			unset($params['key']);
			
			$multiParams = array($params);
		
			$numbers = explode(',', $this->copy);
			foreach ($numbers as $number) {
				$params['to']     = $number;
				$params['to'] = preg_replace("/[^0-9+]/", '', $params['to']);
				
				$multiParams[] = $params;
			}
			
			$ret[] = $this->requestMulti($method, $multiParams);
		} else {
			$ret[] = $this->request('send', $params);
		}
		
		return $ret;
	}
   
	private function request($method, $params) {
		$url = $this->baseurl . $method;
		
		$post = http_build_query($params, '', '&');
		
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			$buffer = curl_exec($ch);
			curl_close($ch);
		} else {
			$context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $post,
                    'timeout' => 10,
                ),
            ));
            $buffer = file_get_contents($url, false, $context);
		}
		
		return $buffer;
	}
	
	private function requestMulti($method, $params) {
		$url = $this->baseurl . $method;
		
		$post = json_encode($params);
		
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8'));
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			$buffer = curl_exec($ch);
			curl_close($ch);
		} else {
			$context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "application/json;charset=UTF-8\r\n",
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