<?php

	class hmrc_rti_eps extends hmrc_rti { // Employer Payment Summary - Send an EPS instead of an FPS if you've not paid any employees in a tax month.

		private $employees = array();

		public function xsi_path_get() {
			return 'artefacts/2013-14/EPS.xsd';
		}

		public function message_class_get() {
			return 'HMRC-PAYE-RTI-EPS';
		}

		public function request_body_get_xml() {

			if ($this->details['year'] == 2013) {
				$namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/EmployerPaymentSummary/13-14/2';
			} else if ($this->details['year'] == 2014) {
				$namespace = 'http://www.govtalk.gov.uk/taxation/PAYE/RTI/EmployerPaymentSummary/14-15/4';
			} else {
				exit_with_error('Namespace is unknown for year ' . $this->details['year']);
			}

			$period_range = substr($this->details['year'], -2);
			$period_range = $period_range . '-' . ($period_range + 1);

			$xml = '
					<IRenvelope xmlns="' . xml($namespace) . '">' . $this->request_header_get_xml() . '
						<EmployerPaymentSummary>
							<EmpRefs>
								<OfficeNo>' . xml($this->details['tax_office_number']) . '</OfficeNo>
								<PayeRef>' . xml($this->details['tax_office_reference']) . '</PayeRef>
								<AORef>' . xml($this->details['accounts_office_reference']) . '</AORef>';

			if ($this->details['corporation_tax_reference'] != '' && $this->details['year'] >= 2014) {
				$xml .= '
								<COTAXRef>' . xml($this->details['corporation_tax_reference']) . '</COTAXRef>';
			}

			$xml .= '
							</EmpRefs>';

			if (false) {

				$xml .= '
							<NoPaymentForPeriod>yes</NoPaymentForPeriod>'; // No payment due, as no employees paid in this pay period.

			} else {

				$xml .= '
							<RecoverableAmountsYTD>
								<SSPRecovered>'          . xml($this->details['XXX']) . '</SSPRecovered>
								<SMPRecovered>'          . xml($this->details['XXX']) . '</SMPRecovered>
								<OSPPRecovered>'         . xml($this->details['XXX']) . '</OSPPRecovered>
								<SAPRecovered>'          . xml($this->details['XXX']) . '</SAPRecovered>
								<ASPPRecovered>'         . xml($this->details['XXX']) . '</ASPPRecovered>
								<NICCompensationOnSMP>'  . xml($this->details['XXX']) . '</NICCompensationOnSMP>
								<NICCompensationOnOSPP>' . xml($this->details['XXX']) . '</NICCompensationOnOSPP>
								<NICCompensationOnSAP>'  . xml($this->details['XXX']) . '</NICCompensationOnSAP>
								<NICCompensationOnASPP>' . xml($this->details['XXX']) . '</NICCompensationOnASPP>
								<CISDeductionsSuffered>' . xml($this->details['XXX']) . '</CISDeductionsSuffered>
								<NICsHoliday>'           . xml($this->details['XXX']) . '</NICsHoliday>
							</RecoverableAmountsYTD>';

			}

			$xml .= '
							<RelatedTaxYear>' . xml($period_range) . '</RelatedTaxYear>';

			if (is_array($this->details['final'])) {

				$xml .= '
							<FinalSubmission>
								<ForYear>yes</ForYear>
							</FinalSubmission>
							<QuestionsAndDeclarations>
								<FreeOfTaxPaymentsMadeToEmployee>'              . xml($this->details['final']['free_of_tax_payments']         ? 'yes' : 'no') . '</FreeOfTaxPaymentsMadeToEmployee>
								<ExpensesVouchersOrBenefitsFromOthers>'         . xml($this->details['final']['expenses_and_benefits']        ? 'yes' : 'no') . '</ExpensesVouchersOrBenefitsFromOthers>
								<PersonEmployedOutsideUKWorkedFor30DaysOrMore>' . xml($this->details['final']['employees_out_of_uk']          ? 'yes' : 'no') . '</PersonEmployedOutsideUKWorkedFor30DaysOrMore>
								<PayToSomeoneElse>'                             . xml($this->details['final']['employees_pay_to_third_party'] ? 'yes' : 'no') . '</PayToSomeoneElse>
								<P11DFormsDue>'                                 . xml($this->details['final']['p11d_forms_due']               ? 'yes' : 'no') . '</P11DFormsDue>
								<ServiceCompany>'                               . xml($this->details['final']['service_company']              ? 'yes' : 'no') . '</ServiceCompany>
							</QuestionsAndDeclarations>';

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