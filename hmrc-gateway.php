<?php

	class hmrc_gateway extends check {

		private $log_db = NULL;
		private $log_table_sql = NULL;
		private $gateway_live = false;
		private $gateway_test = false; // aka "Test in live"
		private $gateway_url = NULL;
		private $message_class = NULL;
		private $message_transation = NULL;
		private $vendor_code = NULL;
		private $vendor_name = NULL;
		private $sender_name = NULL;
		private $sender_pass = NULL;
		private $sender_email = NULL;
		private $response_code = NULL;
		private $response_string = NULL;
		private $response_object = NULL;
		private $response_debug = NULL;
		private $response_qualifier = NULL;
		private $response_function = NULL;
		private $response_correlation = NULL;

		public function __construct() {
		}

		public function live_set($live_server, $live_run) {
			$this->gateway_live = $live_server;
			$this->gateway_test = (!$live_run);
		}

		public function log_table_set($db, $table) {
			$this->log_db = $db;
			$this->log_table_sql = $db->escape_table($table);
		}

		public function submission_url_get() {
			return ($this->gateway_live ? 'https://secure.gateway.gov.uk/submission' : 'https://secure.dev.gateway.gov.uk/submission');
		}

		public function vendor_set($vendor_code, $vendor_name) {
			$this->vendor_code = $vendor_code;
			$this->vendor_name = $vendor_name;
		}

		public function sender_set($sender_name, $sender_pass, $sender_email) {
			$this->sender_name = $sender_name;
			$this->sender_pass = $sender_pass;
			$this->sender_email = $sender_email;
		}

		public function request_submit($request) {

			//--------------------------------------------------
			// Message class

				$this->message_class = $request->message_class_get();

				if ($this->gateway_test) {
					$this->message_class .= '-TIL';
				}

			//--------------------------------------------------
			// Setup message

				$this->gateway_url = $this->submission_url_get();

				$body_xml = $request->request_body_get_xml();

				$message = new hmrc_gateway_message();
				$message->vendor_set($this->vendor_code, $this->vendor_name);
				$message->sender_set($this->sender_name, $this->sender_pass, $this->sender_email);
				$message->message_qualifier_set('request');
				$message->message_function_set('submit');
				$message->message_live_set($this->gateway_live);
				$message->message_keys_set($request->message_keys_get());
				$message->body_set_xml($body_xml);

			//--------------------------------------------------
			// Send

				$this->_send($message, $request->xsi_path_get());

			//--------------------------------------------------
			// Response

				if ($this->response_qualifier == 'acknowledgement') {

					$interval = strval($this->response_object->Header->MessageDetails->ResponseEndPoint['PollInterval']);

					return array(
							'class' => $this->message_class,
							'correlation' => $this->response_correlation,
							'transaction' => $this->message_transation, // Node is blank in response for some reason.
							'endpoint' => strval($this->response_object->Header->MessageDetails->ResponseEndPoint),
							'timeout' => (time() + $interval),
							'status' => NULL,
							'response' => NULL,
						);

				} else {

					exit_with_error('Invalid response from HMRC', $this->response_debug);

				}

		}

		public function request_list($message_class) {

			//--------------------------------------------------
			// Message class

				$this->message_class = $message_class;

				if ($this->gateway_test) {
					$this->message_class .= '-TIL';
				}

			//--------------------------------------------------
			// Setup message

				$this->gateway_url = $this->submission_url_get();

				$body_xml = ''; // or could be '<IncludeIdentifiers>1</IncludeIdentifiers>'

				$message = new hmrc_gateway_message();
				$message->vendor_set($this->vendor_code, $this->vendor_name);
				$message->sender_set($this->sender_name, $this->sender_pass, $this->sender_email);
				$message->message_qualifier_set('request');
				$message->message_function_set('list');
				$message->message_live_set($this->gateway_live);
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
								'timeout' => time(),
								'status' => strval($request->Status),
								'response' => NULL,
							);

					}
				} else {

					exit_with_error('Invalid response from HMRC', $this->response_debug);

				}

				return $requests;

		}

		public function request_poll($request) {

			//--------------------------------------------------
			// Honour timeout

				$timeout = ($request['timeout'] - time());
				if ($timeout > 0) {
					sleep($timeout);
				}

			//--------------------------------------------------
			// Setup message

				$this->message_class = $request['class'];
				$this->gateway_url = $request['endpoint'];

				$message = new hmrc_gateway_message();
				$message->vendor_set($this->vendor_code, $this->vendor_name);
				$message->message_qualifier_set('poll');
				$message->message_function_set('submit');
				$message->message_correlation_set($request['correlation']);

			//--------------------------------------------------
			// Send

				$this->_send($message);

			//--------------------------------------------------
			// Result

				if ($this->response_qualifier == 'error') {

					exit_with_error('Error from gateway "' . $this->response_object->Body->ErrorResponse->Error->Text . '"', $this->response_debug);

				} else if ($this->response_qualifier == 'acknowledgement') {

					$interval = strval($this->response_object->Header->MessageDetails->ResponseEndPoint['PollInterval']);

					return array(
							'class' => $this->message_class,
							'correlation' => $request['correlation'],
							'transaction' => strval($this->response_object->Header->MessageDetails->TransactionID),
							'endpoint' => strval($this->response_object->Header->MessageDetails->ResponseEndPoint),
							'timeout' => (time() + $interval),
							'status' => NULL,
							'response' => NULL,
						);

				} else if ($this->response_qualifier == 'response') {

					return array(
							'class' => $this->message_class,
							'correlation' => $request['correlation'],
							'transaction' => strval($this->response_object->Header->MessageDetails->TransactionID),
							'endpoint' => strval($this->response_object->Header->MessageDetails->ResponseEndPoint),
							'timeout' => time(),
							'status' => 'SUBMISSION_RESPONSE',
							'response' => $this->response_string,
						);

				} else {

					exit_with_error('Invalid qualifier from HMRC', $this->response_debug);

				}

		}

		public function request_delete($request) {

			//--------------------------------------------------
			// Setup message

				$this->message_class = $request['class'];
				$this->gateway_url = $this->submission_url_get();

				$message = new hmrc_gateway_message();
				$message->vendor_set($this->vendor_code, $this->vendor_name);
				$message->message_qualifier_set('request');
				$message->message_function_set('delete');
				$message->message_live_set($this->gateway_live);
				$message->message_correlation_set($request['correlation']);

			//--------------------------------------------------
			// Send

				$this->_send($message);

			//--------------------------------------------------
			// Verify

				if ($this->response_correlation != $request['correlation']) {
					exit_with_error('Did not delete correlation "' . $request['correlation'] . '"', $this->response_debug);
				}

		}

		private function _send($message, $xsi_path = NULL) {

			//--------------------------------------------------
			// Message details

				$this->message_transation = str_replace('.', '', microtime(true)); // uniqid();

				$message->message_class_set($this->message_class);
				$message->message_transation_set($this->message_transation);

				$message_xml = $message->xml_get();
				$message_correlation = $message->message_correlation_get();

			//--------------------------------------------------
			// IRMark

				if (preg_match('/(<IRmark Type="generic">)[^<]*(<\/IRmark>)/', $message_xml, $matches)) {

					$message_xml_clean = str_replace($matches[0], '', $message_xml);

					if (preg_match('/<GovTalkMessage( xmlns="[^"]+")>/', $message_xml, $namespace_matches)) {
						$message_namespace = $namespace_matches[1];
					} else {
						$message_namespace = '';
					}

					$message_xml_clean = preg_replace('/^.*<Body>(.*)<\/Body>.*$/s', '<Body' . $message_namespace . '>$1</Body>', $message_xml_clean);

					$message_xml_dom = new DOMDocument;
					$message_xml_dom->loadXML($message_xml_clean);

					$message_irmark = base64_encode(sha1($message_xml_dom->documentElement->C14N(), true));

					$message_xml = str_replace($matches[0], $matches[1] . $message_irmark . $matches[2], $message_xml);

				} else {

					$message_irmark = NULL;

				}

			//--------------------------------------------------
			// Validation

				if ($xsi_path && false) { // TODO

					$xsi_path = dirname(__FILE__) . '/' . $xsi_path;

					$validate_xml = $message->body_get_xml();
					// $validate_xml = $message->xml_get();

					$validate = new DOMDocument();
					$validate->loadXML($validate_xml);

					if (!$validate->schemaValidate($xsi_path)) {
						exit_with_error('Invalid XML according to XSI file', $validate_xml);
					}

				}

			//--------------------------------------------------
			// Log create

				if ($this->log_table_sql) {

					$this->log_db->insert($this->log_table_sql, array(
							'request_url' => $this->gateway_url,
							'request_xml' => $message_xml,
							'request_correlation' => strval($message_correlation), // Not NULL
							'request_irmark' => strval($message_irmark),
							'request_date' => date('Y-m-d H:i:s'),
						));

					$log_id = $this->log_db->insert_id();

					$log_where_sql = '
						id = "' . $this->log_db->escape($log_id) . '"';

				}

			//--------------------------------------------------
			// Setup socket - similar to curl

				$socket = new socket();
				$socket->exit_on_error_set(false);
				$socket->header_set('Content-Type', 'text/xml; charset=' . head(config::get('output.charset')));

			//--------------------------------------------------
			// Send request

					// header('Content-Type: text/xml; charset=UTF-8');
					// exit($message_xml);

				$send_result = $socket->post($this->gateway_url, $message_xml);

				if (!$send_result) {
					exit_with_error('Could not connect to HMRC', $socket->error_string_get());
				}

				if ($socket->response_code_get() != 200) {
					exit_with_error('Invalid HTTP response from HMRC', $socket->response_full_get());
				}

			//--------------------------------------------------
			// Parse XML

				// if (true) {
				// 	header('Content-Type: text/xml; charset=UTF-8');
				// 	exit($this->response_string);
				// } else {
				// 	$dom_sxe = dom_import_simplexml($this->response_object);
				// 	$dom = new DOMDocument('1.0');
				// 	$dom_sxe = $dom->importNode($dom_sxe, true);
				// 	$dom_sxe = $dom->appendChild($dom_sxe);
				// 	$dom->preserveWhiteSpace = false;
				// 	$dom->formatOutput = true;
				// 	echo $dom->saveXML() . "\n--------------------------------------------------\n\n";
				// 	exit();
				// }

				$this->response_string = $socket->response_data_get();
				$this->response_object = simplexml_load_string($this->response_string);
				$this->response_debug = $this->gateway_url . "\n\n" . $message_xml . "\n\n" . $this->response_string;

			//--------------------------------------------------
			// Update log

				if ($this->log_table_sql) {

					$this->log_db->update($this->log_table_sql, array(
							'response_xml' => $this->response_string,
							'response_date' => date('Y-m-d H:i:s'),
						), $log_where_sql);

				}

			//--------------------------------------------------
			// Extract details

				if (isset($this->response_object->Header->MessageDetails->Qualifier)) {
					$this->response_qualifier = strval($this->response_object->Header->MessageDetails->Qualifier);
				} else {
					exit_with_error('Invalid response from HMRC (qualifier)', $this->response_debug);
				}

				if (isset($this->response_object->Header->MessageDetails->Function)) {
					$this->response_function = strval($this->response_object->Header->MessageDetails->Function);
				} else {
					exit_with_error('Invalid response from HMRC (function)', $this->response_debug);
				}

				if (isset($this->response_object->Header->MessageDetails->CorrelationID)) {
					$this->response_correlation = strval($this->response_object->Header->MessageDetails->CorrelationID);
				} else {
					exit_with_error('Invalid response from HMRC (correlation)', $this->response_debug);
				}

			//--------------------------------------------------
			// Update log (additional details)

				if ($this->log_table_sql) {

					$this->log_db->update($this->log_table_sql, array(
							'response_qualifier' => $this->response_qualifier,
							'response_function' => $this->response_function,
							'response_correlation' => $this->response_correlation,
						), $log_where_sql);

				}

			//--------------------------------------------------
			// Check correlation

				if ($message_correlation !== NULL && $this->response_correlation != $message_correlation) {
					exit_with_error('Invalid response correlation "' . $message_correlation . '"', $this->response_debug);
				}

		}

	}

?>