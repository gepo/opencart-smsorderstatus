<?php
final class Gntext extends SmsGate {
	private $baseurl = "https://www.textapp.net/webservice/httpservice.aspx";
	
	public function send() {
		$ret = array();
		
		$this->to = preg_replace('/[^0-9+]/', '', $this->to);
		
		$params = array(
			'externalLogin'	=> $this->username,
			'password'	=> $this->password,
			'originator'	=> $this->from,
			'destinations'	=> $this->to,
      'body'	=> $this->message
			//'text'	=> $this->message
		);

		$ret[] = $this->request('sendsms', $params);
		
		if ($this->copy) {
			$numbers = explode(',', $this->copy);
			foreach ($numbers as $number) {
				$params['to']     = $number;

				$ret[] = $this->request('sendsms', $params);
			}
		}
		
		return $ret;
	}
   
	private function request($method, $params) {
		$url = $this->baseurl;
		
		$params['method'] = $method;
		
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