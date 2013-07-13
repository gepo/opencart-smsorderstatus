<?php
final class Alphasms_com_ua extends SmsGate {
	private $baseurl = "http://alphasms.com.ua/api/";
	
	public function send() {
		$results = array();

		$data = array(
			'version'  => 'http',
			'key'    => $this->username,
			'command'  => 'send',
			'from'     => $this->from,
			'to'       => substr($this->to, 0, 11),
			'message'      => $this->message
		);

		$results[] = $this->request('http.php', $data);

		if ($this->copy) {
			$numbers = explode(',', $this->copy);
			foreach ($numbers as $number) {
				$data['to']     = substr($number, 0, 11);

				$results[] = $this->process($data);
			}
		}

		return $results;
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