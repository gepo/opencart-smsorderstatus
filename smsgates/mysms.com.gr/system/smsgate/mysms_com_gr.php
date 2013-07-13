<?php
final class mysms_com_gr extends SmsGate {

	public function send() {
		$url = "http://www.mysms.com.gr/api.php";
		
		$params = array (
			'username' => $this->username,
			'password' => $this->password,
			'from' => $this->from,
			'to' => $this->to,
			'message' => $this->message,
		);
		
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
                    'content' => $postFields,
                    'timeout' => 10,
                ),
            ));
            $buffer = file_get_contents($url, false, $context);
		}
		
		if(empty($buffer)) {
			return "response from mysms.com.gr is empty";
		} else {
			return $buffer;
		}
	}
}
?>
