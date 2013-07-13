<?php
final class mvaayoo extends SmsGate {

	public function send() {
		$url = "http://api.mVaayoo.com/mvaayooapi/MessageCompose";
		
		$params = array (
			'user' => $this->username . ':' . $this->password,
			'senderID' => $this->from,
			'receipientno' => $this->to,
			'msgtxt' => $this->message,
		);
		
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
