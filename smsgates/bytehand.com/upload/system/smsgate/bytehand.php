<?php
final class Bytehand extends SmsGate {
	public function send() {	
		$results = array();		
		$data = array(
			'id'	=> $this->username,
			'key'	=> $this->password,
			'from'	=> $this->from,
			'to'	=> $this->to,
			'text'	=> $this->message
		);

		$results[] = $this->process($data);

		if ($this->copy) {
			$numbers = explode(',', $this->copy);
			foreach ($numbers as $number) {
				$data['to']     = $number;

				$results[] = $this->process($data);
			}
		}
		return $results;
	}

	private function process($data) {
		$url = "http://bytehand.com:3800/send?". http_build_query($data);

        return $response = @file_get_contents($url, false);
   }
}
?>