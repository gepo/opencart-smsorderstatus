<?php
final class pennytel extends SmsGate {

	public function send() {
		require_once(DIR_SYSTEM . 'library/nusoap/nusoap.php');
		
		$client = new nusoap_client(
			'http://pennytel.com/pennytelapi/services/PennyTelAPI?wsdl',
			'wsdl'
		);
		
		$err = $client->getError();
		if ($err) {
			return $err;
		}

		$param = array(
			'id' => $this->username,
			'password' => $this->password,
			'type' => 1, // sms_type, 0 - free, 1 - premium
			'to' => $this->to,
			'message' => $this->message,
			'date' => '2012-01-01T00:00:00',
		);
		$result = $client->call('sendSMS', array($param));

		if ($client->fault) {
			return 'Fault: ' . print_r($result, true) . '. Response: ' . $client->response . '. Debug: ' . $client->debug_str;
		} else {
			// Check for errors
			$err = $client->getError();
			if ($err) {
				// Display the error
				return 'Error: ' . $err;
			} else {
				return $result;
			}
		}
	}
}
?>
