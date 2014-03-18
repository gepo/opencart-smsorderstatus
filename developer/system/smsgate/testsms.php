<?php
final class TestSms extends SmsGate {
	public function send() {
		global $log;
		
		$log->write('SMS sent: t=' . $this->to . ';c=' . $this->copy . ';m=' . $this->message);
		
		return 'ok';
	}
}
?>