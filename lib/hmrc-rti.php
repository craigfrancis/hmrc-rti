<?php

	class hmrc_rti extends check { // Employer Alignment Submission

		protected $details = array();
		protected $message_keys = array();

		public function message_keys_set($message_keys) {
			$this->message_keys = $message_keys;
		}

		public function details_set($details) {

			$this->details = array_merge(array(
					'year' => NULL,
					'currency' => 'GBP',
					'sender' => 'Employer',
				), $details);

		}

		public function request_header_get_xml() {

			$period_end = ($this->details['year'] + 1) . '-04-05';

			$xml = '<IRheader>
							<Keys>';

			foreach ($this->message_keys as $key_name => $key_value) {
				$xml .= '
								<Key Type="' . xml($key_name) . '">' . xml($key_value) . '</Key>';
			}

			$xml .= '
							</Keys>
							<PeriodEnd>' . xml($period_end) . '</PeriodEnd>
							<DefaultCurrency>' . xml($this->details['currency']) . '</DefaultCurrency>
							<IRmark Type="generic">XXX</IRmark>
							<Sender>' . xml($this->details['sender']) . '</Sender>
						</IRheader>';

			return $xml;

		}

	}

?>