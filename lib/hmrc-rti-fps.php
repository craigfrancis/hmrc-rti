<?php

	class hmrc_rti_fps extends hmrc_rti { // Full Payment Submission

		private $employees = array();

		public function xsi_path_get() {
			return '/artefacts/2013-14/FPS.xsd';
		}

		public function message_class_get() {
			return 'HMRC-PAYE-RTI-FPS';
		}

		public function employee_add($details) {

			$this->employees[] = array_merge(array(
					'national_insurance_number' => NULL,
					'name' => NULL,
					'address' => NULL,
					'birth_date' => NULL,
					'gender' => NULL,
					'pay_id' => NULL,
					'to_date_taxable' => NULL,
					'to_date_tax' => NULL,
					'payment_freqency' => NULL,
					'payment_date' => NULL,
					'payment_month' => NULL,
					'payment_periods' => NULL,
					'payment_hours' => NULL,
					'payment_tax_code' => NULL,
					'payment_taxable' => NULL,
					'payment_tax' => NULL,
					'ni_letter' => NULL,
					'ni_gross_nics_pd' => NULL,
					'ni_gross_nics_ytd' => NULL,
					'ni_total_lel_ytd' => NULL,
					'ni_total_pt_ytd' => NULL,
					'ni_total_uap_ytd' => NULL,
					'ni_total_uel_ytd' => NULL,
					'ni_total_nic_pd' => NULL,
					'ni_total_nic_ytd' => NULL,
					'ni_total_contribution_pd' => NULL,
					'ni_total_contribution_ytd' => NULL,
				), $details);

		}

		public function request_body_get_xml() {

			if ($this->details['year'] == 2013) {
				$namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/FullPaymentSubmission/13-14/2';
			} else if ($this->details['year'] == 2014) {
				$namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/FullPaymentSubmission/14-15/4';
			} else {
				exit_with_error('Namespace is unknown for year ' . $this->details['year']);
			}

			$period_range = substr($this->details['year'], -2);
			$period_range = $period_range . '-' . ($period_range + 1);

			$xml = '
					<IRenvelope xmlns="' . xml($namespace) . '">
						' . $this->request_header_get_xml() . '
						<FullPaymentSubmission>
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
							<RelatedTaxYear>' . xml($period_range) . '</RelatedTaxYear>';

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
									<FiguresToDate>
										<TaxablePay>' . xml($employee['to_date_taxable']) . '</TaxablePay>
										<TotalTax>' . xml($employee['to_date_tax']) . '</TotalTax>
									</FiguresToDate>
									<Payment>
										<PayFreq>' . xml($employee['payment_freqency']) . '</PayFreq>
										<PmtDate>' . xml($employee['payment_date']) . '</PmtDate>
										<MonthNo>' . xml($employee['payment_month']) . '</MonthNo>
										<PeriodsCovered>' . xml($employee['payment_periods']) . '</PeriodsCovered>
										<HoursWorked>' . xml($employee['payment_hours']) . '</HoursWorked>
										<TaxCode>' . xml($employee['payment_tax_code']) . '</TaxCode>
										<TaxablePay>' . xml($employee['payment_taxable']) . '</TaxablePay>
										<TaxDeductedOrRefunded>' . xml($employee['payment_tax']) . '</TaxDeductedOrRefunded>
									</Payment>
									<NIlettersAndValues>
										<NIletter>' . xml($employee['ni_letter']) . '</NIletter>
										<GrossEarningsForNICsInPd>' . xml($employee['ni_gross_nics_pd']) . '</GrossEarningsForNICsInPd>
										<GrossEarningsForNICsYTD>' . xml($employee['ni_gross_nics_ytd']) . '</GrossEarningsForNICsYTD>
										<AtLELYTD>' . xml($employee['ni_total_lel_ytd']) . '</AtLELYTD>
										<LELtoPTYTD>' . xml($employee['ni_total_pt_ytd']) . '</LELtoPTYTD>
										<PTtoUAPYTD>' . xml($employee['ni_total_uap_ytd']) . '</PTtoUAPYTD>
										<UAPtoUELYTD>' . xml($employee['ni_total_uel_ytd']) . '</UAPtoUELYTD>
										<TotalEmpNICInPd>' . xml($employee['ni_total_nic_pd']) . '</TotalEmpNICInPd>
										<TotalEmpNICYTD>' . xml($employee['ni_total_nic_ytd']) . '</TotalEmpNICYTD>
										<EmpeeContribnsInPd>' . xml($employee['ni_total_contribution_pd']) . '</EmpeeContribnsInPd>
										<EmpeeContribnsYTD>' . xml($employee['ni_total_contribution_ytd']) . '</EmpeeContribnsYTD>
									</NIlettersAndValues>
								</Employment>
							</Employee>';

			}

			$xml .= '
						</FullPaymentSubmission>
					</IRenvelope>';

			return $xml;

		}

	}

?>