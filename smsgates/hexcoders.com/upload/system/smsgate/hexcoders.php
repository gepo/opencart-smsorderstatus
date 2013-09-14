<?php
final class hexcoders extends SmsGate {

	public function send() {
		$url = "http://59.162.167.52/api/MessageCompose";
		
		$params = array (
			'admin' => 'narang.gourav@gmail.com',
			'user' => $this->username . ':' . $this->password,
			'senderID' => $this->from,
			'receipientno' => $this->to,
			'msgtxt' => $this->message,
		);

		$params['receipientno'] = preg_replace("/[^0-9]/", '', $params['receipientno']);
		$post = http_build_query($params, '', '&');
		/*
		$postFields = "user=" . $this->username . ":" . $this->password;
		$postFields .= "&senderID=" . $this->from;
		$postFields .= "&receipientno=" . $this->to;
		$postFields .= "&msgtxt=" . $this->message . "&state=4";
		*/
		
		if (function_exists('curl_init')) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
			curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
			$buffer = curl_exec($ch);
			curl_close($ch);
		} else {
			$context = stream_context_create(array(
                'http' => array(
                    'method' => 'POST',
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'content' => $postFields,
                    'timeout' => 10,
                ),
            ));
            $buffer = file_get_contents($url, false, $context);
		}
		
		if(empty($buffer)) {
			return "response from mvaayoo is empty";
		} else {
			return $buffer;
		}
	}
}
?>
