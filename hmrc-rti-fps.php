<?php

	class hmrc_rti_fps extends hmrc_rti { // Full Payment Submission

		private $employees = array();

		public function xsi_path_get() {
			return 'artefacts/2013-14/FPS.xsd';
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
					'payment_frequency' => NULL,
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
			} else if ($this->details['year'] == 2015) {
				$namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/FullPaymentSubmission/15-16/1';
			} else if ($this->details['year'] == 2016) {
				$namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/FullPaymentSubmission/16-17/2';
			} else {
				exit_with_error('Namespace is unknown for year ' . $this->details['year']);
			}

			$period_range = substr($this->details['year'], -2);
			$period_range = $period_range . '-' . ($period_range + 1);

			$xml = '
					<IRenvelope xmlns="' . xml($namespace) . '">' . $this->request_header_get_xml() . '
						<FullPaymentSubmission>
							<EmpRefs>
								<OfficeNo>' . xml($this->details['tax_office_number']) . '</OfficeNo>
								<PayeRef>' . xml($this->details['tax_office_reference']) . '</PayeRef>
								<AORef>' . xml($this->details['accounts_office_reference']) . '</AORef>';

			if ($this->details['corporation_tax_reference'] != '' && $this->details['year'] >= 2014) {
				$xml .= '
								<COTAXRef>' . xml($this->details['corporation_tax_reference']) . '</COTAXRef>';
			}

			$xml .= '
							</EmpRefs>
							<RelatedTaxYear>' . xml($period_range) . '</RelatedTaxYear>';

			foreach ($this->employees as $employee) {

				if ($this->details['year'] == 2013) {
					if ($employee['payment_hours'] < 16) $payment_hours = 'A';
					else if ($employee['payment_hours'] < 30) $payment_hours = 'B';
					else if ($employee['payment_hours'] < 53) $payment_hours = 'C';
					else $payment_hours = 'D';
				} else {
					if ($employee['payment_hours'] < 16) $payment_hours = 'A';
					else if ($employee['payment_hours'] < 24) $payment_hours = 'B';
					else if ($employee['payment_hours'] < 30) $payment_hours = 'C';
					else if ($employee['payment_hours'] < 53) $payment_hours = 'D';
					else $payment_hours = 'E';
				}

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
										<PayFreq>' . xml($employee['payment_frequency']) . '</PayFreq>
										<PmtDate>' . xml($employee['payment_date']) . '</PmtDate>
										<MonthNo>' . xml($employee['payment_month']) . '</MonthNo>
										<PeriodsCovered>' . xml($employee['payment_periods']) . '</PeriodsCovered>
										<HoursWorked>' . xml($payment_hours) . '</HoursWorked>
										<TaxCode>' . xml($employee['payment_tax_code']) . '</TaxCode>
										<TaxablePay>' . xml($employee['payment_taxable']) . '</TaxablePay>
										<TaxDeductedOrRefunded>' . xml($employee['payment_tax']) . '</TaxDeductedOrRefunded>';

										// <FlexibleDrawdown>
										// 	<FlexiblyAccessingPensionRights>yes</FlexiblyAccessingPensionRights>
										// 	<PensionDeathBenefit>yes</PensionDeathBenefit>
										// 	<TaxablePayment>600.00</TaxablePayment>
										// 	<NontaxablePayment>1200.00</NontaxablePayment>
										// </FlexibleDrawdown>

						$xml .= '
									</Payment>
									<NIlettersAndValues>
										<NIletter>' . xml($employee['ni_letter']) . '</NIletter>
										<GrossEarningsForNICsInPd>' . xml(number_format($employee['ni_gross_nics_pd'],          2, '.', '')) . '</GrossEarningsForNICsInPd>
										<GrossEarningsForNICsYTD>'  . xml(number_format($employee['ni_gross_nics_ytd'],         2, '.', '')) . '</GrossEarningsForNICsYTD>
										<AtLELYTD>'                 . xml(number_format($employee['ni_total_lel_ytd'],          2, '.', '')) . '</AtLELYTD>
										<LELtoPTYTD>'               . xml(number_format($employee['ni_total_pt_ytd'],           2, '.', '')) . '</LELtoPTYTD>';

						if ($this->details['year'] < 2016) {
							$xml .= '
										<PTtoUAPYTD>'               . xml(number_format($employee['ni_total_uap_ytd'],          2, '.', '')) . '</PTtoUAPYTD>
										<UAPtoUELYTD>'              . xml(number_format($employee['ni_total_uel_ytd'],          2, '.', '')) . '</UAPtoUELYTD>';
						} else {
							$xml .= '
										<PTtoUELYTD>'               . xml(number_format($employee['ni_total_uel_ytd'],          2, '.', '')) . '</PTtoUELYTD>';
						}

						$xml .= '
										<TotalEmpNICInPd>'          . xml(number_format($employee['ni_total_nic_pd'],           2, '.', '')) . '</TotalEmpNICInPd>
										<TotalEmpNICYTD>'           . xml(number_format($employee['ni_total_nic_ytd'],          2, '.', '')) . '</TotalEmpNICYTD>
										<EmpeeContribnsInPd>'       . xml(number_format($employee['ni_total_contribution_pd'],  2, '.', '')) . '</EmpeeContribnsInPd>
										<EmpeeContribnsYTD>'        . xml(number_format($employee['ni_total_contribution_ytd'], 2, '.', '')) . '</EmpeeContribnsYTD>
									</NIlettersAndValues>
								</Employment>
							</Employee>';

			}

			if (is_array($this->details['final'])) {

				$xml .= '
							<FinalSubmission>
								<ForYear>yes</ForYear>
							</FinalSubmission>';

				if ($this->details['year'] < 2016) {

					$xml .= '
							<QuestionsAndDeclarations>
								<FreeOfTaxPaymentsMadeToEmployee>'              . xml($this->details['final']['free_of_tax_payments']         ? 'yes' : 'no') . '</FreeOfTaxPaymentsMadeToEmployee>
								<ExpensesVouchersOrBenefitsFromOthers>'         . xml($this->details['final']['expenses_and_benefits']        ? 'yes' : 'no') . '</ExpensesVouchersOrBenefitsFromOthers>
								<PersonEmployedOutsideUKWorkedFor30DaysOrMore>' . xml($this->details['final']['employees_out_of_uk']          ? 'yes' : 'no') . '</PersonEmployedOutsideUKWorkedFor30DaysOrMore>
								<PayToSomeoneElse>'                             . xml($this->details['final']['employees_pay_to_third_party'] ? 'yes' : 'no') . '</PayToSomeoneElse>
								<P11DFormsDue>'                                 . xml($this->details['final']['p11d_forms_due']               ? 'yes' : 'no') . '</P11DFormsDue>
								<ServiceCompany>'                               . xml($this->details['final']['service_company']              ? 'yes' : 'no') . '</ServiceCompany>
							</QuestionsAndDeclarations>';

				}

			} else if ($this->details['final'] !== false) {

				exit_with_error('Invalid "final" value (should be false, or an array)');

			}

			$xml .= '
						</FullPaymentSubmission>
					</IRenvelope>';

			return $xml;

		}

	}

?>