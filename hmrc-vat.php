<?php

	class hmrc_rti extends check {

		protected $details = array();

		public function details_set($details) {

			$this->details = array_merge(array(
					'year' => NULL,
					'month' => NULL,
					'sender' => 'Company',
				), $details);

		}

		public function message_keys_get() {

			return array(
					'VATRegNo' => $this->details['vat_registration_number'],
				);

		}

		public function request_header_get_xml() {

			$period_id = intval($this->details['year']) . '-' . str_pad(intval($this->details['year']), 2, '0', STR_PAD_LEFT);

			$xml = '
						<IRheader>
							<Keys>';

			foreach ($this->message_keys_get() as $key_name => $key_value) {
				$xml .= '
								<Key Type="' . xml($key_name) . '">' . xml($key_value) . '</Key>';
			}

			$xml .= '
							</Keys>
							<PeriodID>' . xml($period_id) . '</PeriodID>
							<IRmark Type="generic">XXX</IRmark>
							<Sender>' . xml($this->details['sender']) . '</Sender>
						</IRheader>';

			return $xml;

		}

		public function message_class_get() {
			return 'HMRC-VAT-DEC';
		}

		public function request_body_get_xml() {

				// https://www.gov.uk/government/publications/vat-returns-and-ec-sales-lists-online-vat

				// Box 1 - VAT due in this period on sales and other outputs
				// Box 2 - VAT due in this period on acquisitions from other EC Member States
				// Box 3 - Total VAT due (the sum of boxes 1 and 2)
				// Box 4 - VAT reclaimed in this period on purchases and other inputs, (including acquisitions from the EC)
				// Box 5 - Net VAT to be paid to HM Revenue & Customs or reclaimed by you (Difference between boxes 3 and 4)
				// Box 6 - Total value of sales and all other outputs excluding any VAT. Include your box 8 figure
				// Box 7 - Total value of purchases and all other inputs excluding any VAT. Include your box 9 figure
				// Box 8 - Total value of all supplies of goods and related costs, excluding any VAT, to other EC Member States
				// Box 9 - Total value of all acquisitions of goods and related costs, excluding any VAT, from other EC Member States

			$namespace = 'http://www.govtalk.gov.uk/taxation/vat/vatdeclaration/2';

			$xml = '
					<IRenvelope xmlns="' . xml($namespace) . '">' . $this->request_header_get_xml() . '
						<VATDeclarationRequest>
							<VATDueOnOutputs>' . xml($this->details['vat_due_on_output']) . '</VATDueOnOutputs>
							<VATDueOnECAcquisitions>0.50</VATDueOnECAcquisitions>
							<TotalVAT>' . xml($this->details['vat_total']) . '</TotalVAT>
							<VATReclaimedOnInputs>2.00</VATReclaimedOnInputs>
							<NetVAT>0.00</NetVAT>
							<NetSalesAndOutputs>20</NetSalesAndOutputs>
							<NetPurchasesAndInputs>10</NetPurchasesAndInputs>
							<NetECSupplies>10</NetECSupplies>
							<NetECAcquisitions>5</NetECAcquisitions>
						</VATDeclarationRequest>
					</IRenvelope>';

			return $xml;

		}

	}

?>