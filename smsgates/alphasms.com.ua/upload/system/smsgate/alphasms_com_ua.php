<?php
final class Alphasms_com_ua extends SmsGate {

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

		$results[] = $this->process($data);

		if ($this->copy) {
			$numbers = explode(',', $this->copy);
			foreach ($numbers as $number) {
				$data['to']     = substr($number, 0, 11);

				$results[] = $this->process($data);
			}
		}

		return $results;
	}

	private function process($data) {
		$url = "http://alphasms.com.ua/api/http.php?" . http_build_query($data);

		return @file_get_contents($url);
	}
}
?>