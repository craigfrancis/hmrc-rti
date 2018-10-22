<?php

	class hmrc_gateway_message extends check {

		private $message_live = NULL;
		private $message_class = NULL;
		private $message_qualifier = NULL;
		private $message_function = NULL;
		private $message_transation = NULL;
		private $message_correlation = NULL;
		private $message_keys = array();
		private $vendor_code = NULL;
		private $vendor_name = NULL;
		private $sender_name = NULL;
		private $sender_pass = NULL;
		private $sender_email = NULL;
		private $body_xml = '';

		public function __construct() {
		}

		public function message_live_set($message_live) {
			$this->message_live = $message_live;
		}

		public function message_class_set($message_class) {
			$this->message_class = $message_class;
		}

		public function message_qualifier_set($message_qualifier) {
			$this->message_qualifier = $message_qualifier;
		}

		public function message_function_set($message_function) {
			$this->message_function = $message_function;
		}

		public function message_transation_set($message_transation) {
			$this->message_transation = $message_transation;
		}

		public function message_correlation_set($message_correlation) {
			$this->message_correlation = $message_correlation;
		}

		public function message_correlation_get() {
			return $this->message_correlation;
		}

		public function message_keys_set($message_keys) {
			$this->message_keys = $message_keys;
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

		public function body_set_xml($body_xml) {
			$this->body_xml = $body_xml;
		}

		public function body_get_xml() {
			return $this->body_xml;
		}

		public function xml_get() {

			// $sender_pass = base64_encode(md5(strtolower($this->sender_pass), true)); // MD5 was the only hashing option, and it needed to be lower cased first.

			$xml = '<?xml version="1.0"?>
					<GovTalkMessage xmlns="http://www.govtalk.gov.uk/CM/envelope">
						<EnvelopeVersion>2.0</EnvelopeVersion>
						<Header>
							<MessageDetails>
								<Class>' . xml($this->message_class) . '</Class>
								<Qualifier>' . xml($this->message_qualifier) . '</Qualifier>
								<Function>' . xml($this->message_function) . '</Function>
								<TransactionID>' . xml($this->message_transation) . '</TransactionID>
								<CorrelationID>' . xml($this->message_correlation) . '</CorrelationID>
								<Transformation>XML</Transformation>';

			if ($this->message_live !== NULL) {
				$xml .= '
								<GatewayTest>' . xml($this->message_live ? '0' : '1') . '</GatewayTest>';
			}

			$xml .= '
								<GatewayTimestamp></GatewayTimestamp>
							</MessageDetails>';

			if ($this->sender_name !== NULL) {
				$xml .= '
							<SenderDetails>
								<IDAuthentication>
									<SenderID>' . xml($this->sender_name) . '</SenderID>
									<Authentication>
										<Method>clear</Method>
										<Value>' . xml($this->sender_pass) . '</Value>
									</Authentication>
								</IDAuthentication>
								<EmailAddress>' . xml($this->sender_email) . '</EmailAddress>
							</SenderDetails>';
			}

			$xml .= '
						</Header>
						<GovTalkDetails>
							<Keys>';

			foreach ($this->message_keys as $key_name => $key_value) {
				$xml .= '
								<Key Type="' . xml($key_name) . '">' . xml($key_value) . '</Key>';
			}

			$xml .= '
							</Keys>
							<ChannelRouting>
								<Channel>
									<URI>' . xml($this->vendor_code) . '</URI>
									<Product>' . xml($this->vendor_name) . '</Product>
									<Version>1</Version>
								</Channel>
								<Timestamp>' . xml(date('Y-m-d\TH:i:s')) . '</Timestamp>
							</ChannelRouting>
						</GovTalkDetails>
						<Body>' . $this->body_xml . '</Body>
					</GovTalkMessage>';

			$xml = str_replace("\n\t\t\t\t\t", "\n", $xml);
			$xml = str_replace("\t", '  ', $xml);

			return $xml;

		}

	}

?>