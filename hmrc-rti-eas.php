<?php

	class hmrc_rti_eas extends hmrc_rti { // Employer Alignment Submission

		private $employees = array();

		public function xsi_path_get() {
			return '/artefacts/2013-14/EAS.xsd';
		}

		public function message_class_get() {
			return 'HMRC-PAYE-RTI-EAS';
		}

		public function employee_add($details) {

			$this->employees[] = array_merge(array(
					'national_insurance_number' => NULL,
					'name' => NULL,
					'address' => NULL,
					'birth_date' => NULL,
					'gender' => NULL,
					'pay_id' => NULL,
					'payment_tax_code' => NULL,
				), $details);

		}

		public function request_body_get_xml() {

			$namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/EmployerAlignmentSubmission/3';

			$xml = '
					<IRenvelope xmlns="' . xml($namespace) . '">' . $this->request_header_get_xml() . '
						<EmployerAlignmentSubmission>
							<EmpRefs>
								<OfficeNo>' . xml($this->details['tax_office_number']) . '</OfficeNo>
								<PayeRef>' . xml($this->details['tax_office_reference']) . '</PayeRef>
								<AORef>' . xml($this->details['accounts_office_reference']) . '</AORef>';

			if ($this->details['year'] >= 2014) {
				$xml .= '
								<COTAXRef>' . xml($this->details['corporation_tax_reference']) . '</COTAXRef>';
			}

			$xml .= '
							</EmpRefs>
							<NumberOfParts>1</NumberOfParts>';

			foreach ($this->employees as $employee) {

				$xml .= '
							<Employee>
								<EmployeeDetails>
									<NINO>' . xml($employee['national_insurance_number']) . '</NINO>
									<Name>
										<Ttl>' . xml($employee['name']['title']) . '</Ttl>
										<Fore>' . xml($employee['name']['forename']) . '</Fore>
										<Sur>' . xml($employee['name']['surname']) . '</Sur>
									</Name>
									<Address>';

				foreach ($employee['address']['lines'] as $line) {
					$xml .= '
										<Line>' . xml($line) . '</Line>';
				}

				$xml .= '
										<UKPostcode>' . xml($employee['address']['postcode']) . '</UKPostcode>
									</Address>
									<BirthDate>' . xml($employee['birth_date']) . '</BirthDate>
									<Gender>' . xml($employee['gender']) . '</Gender>
								</EmployeeDetails>
								<Employment>
									<PayId>' . xml($employee['pay_id']) . '</PayId>
									<Payment>
										<TaxCode>' . xml($employee['payment_tax_code']) . '</TaxCode>
									</Payment>
								</Employment>
							</Employee>';

			}

			$xml .= '
						</EmployerAlignmentSubmission>
					</IRenvelope>';

			return $xml;

		}

	}

?>