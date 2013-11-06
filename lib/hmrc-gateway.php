<?php

	class hmrc_gateway extends check {

		private $gateway_live = false;
		private $gateway_test = false; // aka "Test in live"
		private $gateway_url = NULL;
		private $message_class = NULL;
		private $message_keys = array();
		private $sender_name = NULL;
		private $sender_pass = NULL;
		private $sender_email = NULL;
		private $response_code = NULL;
		private $response_string = NULL;
		private $response_object = NULL;

		public function __construct() {
		}

		public function live_set($live_server, $live_run) {
			$this->gateway_live = $live_server;
			$this->gateway_test = (!$live_run);
		}

		public function gateway_url_set($url) {
			$this->gateway_url = $url;
		}

		public function gateway_url_get() {
			if ($this->gateway_url !== NULL) {
				return $this->gateway_url;
			}
			return ($this->gateway_live ? 'https://secure.gateway.gov.uk/submission' : 'https://secure.dev.gateway.gov.uk/submission');
		}

		public function message_class_get() {
			return $this->message_class . ($this->gateway_test ? '-TIL' : '');
		}

		public function message_key_add($name, $value) {
			$this->message_keys[$name] = $value;
		}

		public function sender_set($sender_name, $sender_pass, $sender_email) {
			$this->sender_name = $sender_name;
			$this->sender_pass = $sender_pass;
			$this->sender_email = $sender_email;
		}

		public function request_submit($request) {

			//--------------------------------------------------
			// Setup message

				$this->message_class = $request->message_class_get();

				$message = new hmrc_gateway_message();
				$message->message_qualifier_set('request');
				$message->message_function_set('submit');
				$message->message_live_set($this->gateway_live);
				$message->message_keys_set($this->message_keys);
				$message->sender_set($this->sender_name, $this->sender_pass, $this->sender_email);
				$message->body_set_xml($request->request_body_get_xml());

			//--------------------------------------------------
			// Send

				$this->_send($message);

			//--------------------------------------------------
			// Response

				if (isset($this->response_object->Header->MessageDetails->ResponseEndPoint) && isset($this->response_object->Header->MessageDetails->CorrelationID)) {

					$interval = strval($this->response_object->Header->MessageDetails->ResponseEndPoint['PollInterval']);

					return array(
							'class' => $this->message_class,
							'correlation' => strval($this->response_object->Header->MessageDetails->CorrelationID),
							'transaction' => 'TODO',
							'endpoint' => strval($this->response_object->Header->MessageDetails->ResponseEndPoint),
							'interval' => $interval,
							'timeout' => (time() + $interval),
							'status' => NULL,
						);

				} else {

					exit_with_error('Invalid response from HMRC', $this->response_string);

				}

		}

		public function request_list($message_class) {

			//--------------------------------------------------
			// Setup message

				$this->message_class = $message_class;

				$body_xml = ''; // '<IncludeIdentifiers>1</IncludeIdentifiers>'

				$message = new hmrc_gateway_message();
				$message->message_qualifier_set('request');
				$message->message_function_set('list');
				$message->message_live_set($this->gateway_live);
				$message->sender_set($this->sender_name, $this->sender_pass, $this->sender_email);
				$message->body_set_xml($body_xml);

			//--------------------------------------------------
			// Send

				$this->_send($message);

			//--------------------------------------------------
			// Extract requests

				$requests = array();

				if (isset($this->response_object->Body->StatusReport)) {
					foreach ($this->response_object->Body->StatusReport->StatusRecord as $request) {

						$requests[] = array(
								'class' => $this->message_class,
								'correlation' => strval($request->CorrelationID),
								'transaction' => strval($request->TransactionID),
								'endpoint' => strval($this->response_object->Header->MessageDetails->ResponseEndPoint),
								'interval' => 0,
								'timeout' => time(),
								'status' => strval($request->Status),
							);

					}
				} else {

					exit_with_error('Invalid response from HMRC', $this->response_string);

				}

				return $requests;

		}

		public function request_poll($request) {

			//--------------------------------------------------
			// Honnor timeout

				$timeout = ($request['timeout'] - time());
				if ($timeout > 0) {
					sleep($timeout);
				}

			//--------------------------------------------------
			// Setup message

				$this->message_class = $request['class'];

				$message = new hmrc_gateway_message();
				$message->message_qualifier_set('poll');
				$message->message_function_set('submit');
				$message->message_correlation_set($request['correlation']);

			//--------------------------------------------------
			// Send

				$this->_send($message);

			//--------------------------------------------------
			// Result

				if (isset($this->response_object->Header->MessageDetails->Qualifier)) {

					if (strval($this->response_object->Header->MessageDetails->CorrelationID) != $request['correlation']) {
						exit_with_error('Did not delete correlation "' . $request['correlation'] . '"', $this->response_string);
					}

					$interval = strval($this->response_object->Header->MessageDetails->ResponseEndPoint['PollInterval']);

if ($this->response_object->Header->MessageDetails->Qualifier == 'acknowledgement') {
	$status = NULL;
} else {
	$status = 'done?';
}

					return array(
							'class' => $this->message_class,
							'correlation' => $request['correlation'],
							'transaction' => strval($this->response_object->Header->MessageDetails->TransactionID),
							'endpoint' => strval($this->response_object->Header->MessageDetails->ResponseEndPoint),
							'interval' => $interval,
							'timeout' => (time() + $interval),
							'status' => $status,
						);

				} else {

					exit_with_error('Invalid response from HMRC', $this->response_string);

				}

		}

		public function request_delete($request) {

			//--------------------------------------------------
			// Setup message

				$this->message_class = $request['class'];

				$message = new hmrc_gateway_message();
				$message->message_qualifier_set('request');
				$message->message_function_set('delete');
				$message->message_live_set($this->gateway_live);
				$message->message_correlation_set($request['correlation']);

			//--------------------------------------------------
			// Send

				$this->_send($message);

			//--------------------------------------------------
			// Verify

				$requests = array();

				if (isset($this->response_object->Header->MessageDetails->CorrelationID)) {
					if (strval($this->response_object->Header->MessageDetails->CorrelationID) != $request['correlation']) {
						exit_with_error('Did not delete correlation "' . $request['correlation'] . '"', $this->response_string);
					}
				} else {
					exit_with_error('Invalid response from HMRC', $this->response_string);
				}

		}

		private function _send($message, $response_xsi = NULL) {

			//--------------------------------------------------
			// Additional properties

				$message_transation = str_replace('.', '', microtime(true)); // uniqid();

				$message->message_class_set($this->message_class_get());
				$message->message_transation_set($message_transation);

			//--------------------------------------------------
			// Setup socket - similar to curl

				$socket = new socket();
				$socket->exit_on_error_set(false);
				$socket->header_add('Content-Type', 'text/xml; charset=' . head(config::get('output.charset')));

			//--------------------------------------------------
			// Send request

				$message_xml = $message->xml_get();

echo $message_xml . "\n\n";

				$send_result = $socket->post($this->gateway_url_get(), $message_xml);

				if (!$send_result) {
					exit_with_error('Could not connect to HMRC', $socket->error_string_get());
				}

				if ($socket->response_code_get() != 200) {
					exit_with_error('Invalid response from HMRC', $socket->response_full_get());
				}

			//--------------------------------------------------
			// Parse XML

				$this->response_string = $socket->response_data_get();
				$this->response_object = simplexml_load_string($this->response_string);

$dom_sxe = dom_import_simplexml($this->response_object);
$dom = new DOMDocument('1.0');
$dom_sxe = $dom->importNode($dom_sxe, true);
$dom_sxe = $dom->appendChild($dom_sxe);
$dom->preserveWhiteSpace = false;
$dom->formatOutput = true;
echo $dom->saveXML() . "\n--------------------------------------------------\n\n";

			//--------------------------------------------------
			// Validation

				if ($response_xsi) {
					// TODO
				}

				// $validate = new DOMDocument();
				// $validate->loadXML($this->_fullResponseString);
				// if ($validate->schemaValidate($this->_additionalXsiSchemaLocation)) {
				// 	$validXMLResponse = true;
				// }

		}

	}

?>