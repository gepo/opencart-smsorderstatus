<?php
final class Clickatell extends SmsGate {

	public function send() {
		$api_id = "3421941";
		
		// auth call
		$baseurl ="http://api.clickatell.com";
		$url = "$baseurl/http/auth?user=" . $this->username . "&password=" . $this->password . "&api_id=$api_id";
	 
		// do auth call
		$ret = file($url);
	 
		// explode our response. return string is on first line of the data returned
		$sess = explode(":", $ret[0]);
		if ($sess[0] == "OK") {
			$sess_id = trim($sess[1]); // remove any whitespace
			
			$text = urlencode($this->message);
			$url = "$baseurl/http/sendmsg?session_id=$sess_id&to=" . $this->to . "&text=$text";
	 
			// do sendmsg call
			$ret = file($url);
			$send = explode(":",$ret[0]);
	 
			if ($send[0] == "ID") {
				return "successnmessage ID: ". $send[1];
			} else {
				return "send message failed";
			}
		} else {
			return "Authentication failure: ". $ret[0];
		}
	}
}
?>
