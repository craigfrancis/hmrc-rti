<?php

	class hmrc_vat extends check {

		protected $details = array();

		public function details_set($details) {

			$this->details = array_merge(array(
					'vat_registration_number' => NULL,
					'year'                    => NULL,
					'month'                   => NULL,
					'sender'                  => 'Company', // Options include: Individual, Company, Agent, Bureau, Partnership, Trust, Employer, Government, Acting in Capacity, Other
					'vat_due_output'          => 0, // Box 1 - VAT due in this period on sales and other outputs
					'vat_due_acquisitions'    => 0, // Box 2 - VAT due in this period on acquisitions from other EC Member States
					'vat_total'               => 0, // Box 3 - Total VAT due (the sum of boxes 1 and 2)
					'vat_reclaimed'           => 0, // Box 4 - VAT reclaimed in this period on purchases and other inputs, (including acquisitions from the EC)
					'vat_net'                 => 0, // Box 5 - Net VAT to be paid to HM Revenue & Customs or reclaimed by you (Difference between boxes 3 and 4)
					'total_sales'             => 0, // Box 6 - Total value of sales and all other outputs excluding any VAT. Include your box 8 figure
					'total_purchases'         => 0, // Box 7 - Total value of purchases and all other inputs excluding any VAT. Include your box 9 figure
					'total_supplies'          => 0, // Box 8 - Total value of all supplies of goods and related costs, excluding any VAT, to other EC Member States
					'total_acquisitions'      => 0, // Box 9 - Total value of all acquisitions of goods and related costs, excluding any VAT, from other EC Member States
				), $details);

		}

		public function message_keys_get() {

			return array(
					'VATRegNo' => $this->details['vat_registration_number'],
				);

		}

		public function request_header_get_xml() {

			$period_id = intval($this->details['year']) . '-' . str_pad(intval($this->details['month']), 2, '0', STR_PAD_LEFT);

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

			$namespace = 'http://www.govtalk.gov.uk/taxation/vat/vatdeclaration/2';

			$xml = '
					<IRenvelope xmlns="' . xml($namespace) . '">' . $this->request_header_get_xml() . '
						<VATDeclarationRequest>
							<VATDueOnOutputs>'        . xml($this->format_amount(2, $this->details['vat_due_output']))       . '</VATDueOnOutputs>
							<VATDueOnECAcquisitions>' . xml($this->format_amount(2, $this->details['vat_due_acquisitions'])) . '</VATDueOnECAcquisitions>
							<TotalVAT>'               . xml($this->format_amount(2, $this->details['vat_total']))            . '</TotalVAT>
							<VATReclaimedOnInputs>'   . xml($this->format_amount(2, $this->details['vat_reclaimed']))        . '</VATReclaimedOnInputs>
							<NetVAT>'                 . xml($this->format_amount(2, $this->details['vat_net']))              . '</NetVAT>
							<NetSalesAndOutputs>'     . xml($this->format_amount(0, $this->details['total_sales']))          . '</NetSalesAndOutputs>
							<NetPurchasesAndInputs>'  . xml($this->format_amount(0, $this->details['total_purchases']))      . '</NetPurchasesAndInputs>
							<NetECSupplies>'          . xml($this->format_amount(0, $this->details['total_supplies']))       . '</NetECSupplies>
							<NetECAcquisitions>'      . xml($this->format_amount(0, $this->details['total_acquisitions']))   . '</NetECAcquisitions>
						</VATDeclarationRequest>
					</IRenvelope>';

			return $xml;

		}

		private function format_amount($decimals, $amount) {
			return number_format($amount, $decimals, '.', '');
		}

		public function response_details($response_object) {

			return array(
					'collection_date' => strval($response_object->Body->SuccessResponse->ResponseData->VATDeclarationResponse->Body->PaymentNotification->DirectDebitPaymentStatus->CollectionDate),
				);

		}

	}

?>