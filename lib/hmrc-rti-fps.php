<?php

	class hmrc_rti_fps extends check {

		public function __construct() {
		}

		public function employee_add($name) {
		}

		public function message_class_get() {
			return 'HMRC-PAYE-RTI-FPS';
		}

		public function request_body_get_xml() {
			return '
				<IRenvelope xmlns="http://www.govtalk.gov.uk/taxation/PAYE/RTI/FullPaymentSubmission/13-14/2">
					<IRheader>
						<Keys>
							<Key Type="TaxOfficeNumber">635</Key>
							<Key Type="TaxOfficeReference">A635</Key>
						</Keys>
						<PeriodEnd>2014-04-05</PeriodEnd>
						<DefaultCurrency>GBP</DefaultCurrency>
						<IRmark Type="generic">QfV8qcJuF/uZRbZuvoUjoj+sqBc=</IRmark>
						<Sender>Employer</Sender>
					</IRheader>
					<FullPaymentSubmission>
						<EmpRefs>
							<OfficeNo>635</OfficeNo>
							<PayeRef>A635</PayeRef>
							<AORef>635PC00000000</AORef>
							<ECON>E3567891A</ECON>
						</EmpRefs>
						<RelatedTaxYear>13-14</RelatedTaxYear>
						<Employee>
							<EmployeeDetails>
								<NINO>AB164231A</NINO>
								<Name>
									<Ttl>Mr</Ttl>
									<Fore>Alan</Fore>
									<Sur>Example</Sur>
								</Name>
								<Address>
									<Line>1 The Lane</Line>
									<Line>Shipley</Line>
									<Line>West Yorkshire</Line>
									<UKPostcode>BD17 2AD</UKPostcode>
								</Address>
								<BirthDate>1980-10-28</BirthDate>
								<Gender>M</Gender>
							</EmployeeDetails>
							<Employment>
								<Starter>
									<StartDate>2013-04-08</StartDate>
									<StartDec>B</StartDec>
								</Starter>
								<PayId>123-A03</PayId>
								<FiguresToDate>
									<TaxablePay>2000.00</TaxablePay>
									<TotalTax>384.60</TotalTax>
								</FiguresToDate>
								<Payment>
									<BacsHashCode>1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef</BacsHashCode>
									<PayFreq>M1</PayFreq>
									<PmtDate>2013-05-31</PmtDate>
									<MonthNo>2</MonthNo>
									<PeriodsCovered>1</PeriodsCovered>
									<HoursWorked>C</HoursWorked>
									<TaxCode>45L</TaxCode>
									<TaxablePay>1000.00</TaxablePay>
									<TaxDeductedOrRefunded>192.40</TaxDeductedOrRefunded>
								</Payment>
								<NIlettersAndValues>
									<NIletter>D</NIletter>
									<GrossEarningsForNICsInPd>1000.00</GrossEarningsForNICsInPd>
									<GrossEarningsForNICsYTD>2000.00</GrossEarningsForNICsYTD>
									<AtLELYTD>928.00</AtLELYTD>
									<LELtoPTYTD>340.00</LELtoPTYTD>
									<PTtoUAPYTD>732.00</PTtoUAPYTD>
									<UAPtoUELYTD>0.00</UAPtoUELYTD>
									<TotalEmpNICInPd>33.66</TotalEmpNICInPd>
									<TotalEmpNICYTD>67.32</TotalEmpNICYTD>
									<EmpeeContribnsInPd>36.42</EmpeeContribnsInPd>
									<EmpeeContribnsYTD>72.84</EmpeeContribnsYTD>
								</NIlettersAndValues>
							</Employment>
						</Employee>
						<Employee>
							<EmployeeDetails>
								<Name>
									<Ttl>Mr</Ttl>
									<Fore>John</Fore>
									<Fore>Edward</Fore>
									<Sur>Surname</Sur>
								</Name>
								<Address>
									<Line>45 High Street</Line>
									<Line>Gosforth</Line>
									<Line>Newcastle upon Tyne</Line>
									<Line>Tyne and Wear</Line>
									<UKPostcode>NE1 7XF</UKPostcode>
								</Address>
								<BirthDate>1924-05-11</BirthDate>
								<Gender>M</Gender>
							</EmployeeDetails>
							<Employment>
								<PayId>123-A02</PayId>
								<IrrEmp>yes</IrrEmp>
								<LeavingDate>2013-05-17</LeavingDate>
								<FiguresToDate>
									<TaxablePay>8000.00</TaxablePay>
									<TotalTax>756.76</TotalTax>
									<StudentLoansTD>482.00</StudentLoansTD>
								</FiguresToDate>
								<Payment>
									<BacsHashCode>ef1234567890abcdef1234567890abcdef1234567890abcdef1234567890abcd</BacsHashCode>
									<PayFreq>M1</PayFreq>
									<PmtDate>2013-05-31</PmtDate>
									<MonthNo>2</MonthNo>
									<PeriodsCovered>1</PeriodsCovered>
									<HoursWorked>B</HoursWorked>
									<TaxCode>810L</TaxCode>
									<TaxablePay>4000.00</TaxablePay>
									<StudentLoanRecovered>241.00</StudentLoanRecovered>
									<TaxDeductedOrRefunded>756.76</TaxDeductedOrRefunded>
								</Payment>
								<NIlettersAndValues>
									<NIletter>C</NIletter>
									<GrossEarningsForNICsInPd>4000.00</GrossEarningsForNICsInPd>
									<GrossEarningsForNICsYTD>8000.00</GrossEarningsForNICsYTD>
									<AtLELYTD>928.00</AtLELYTD>
									<LELtoPTYTD>340.00</LELtoPTYTD>
									<PTtoUAPYTD>5406.00</PTtoUAPYTD>
									<UAPtoUELYTD>406.00</UAPtoUELYTD>
									<TotalEmpNICInPd>465.88</TotalEmpNICInPd>
									<TotalEmpNICYTD>931.76</TotalEmpNICYTD>
									<EmpeeContribnsInPd>0.00</EmpeeContribnsInPd>
									<EmpeeContribnsYTD>0.00</EmpeeContribnsYTD>
								</NIlettersAndValues>
							</Employment>
						</Employee>
						<Employee>
							<EmployeeDetails>
								<NINO>NS341264D</NINO>
								<Name>
									<Ttl>Miss</Ttl>
									<Fore>Belinda</Fore>
									<Fore>Jo</Fore>
									<Sur>Test</Sur>
								</Name>
								<BirthDate>1958-08-17</BirthDate>
								<Gender>F</Gender>
							</EmployeeDetails>
							<Employment>
							<OccPenInd>yes</OccPenInd>
								<PayId>123-A01</PayId>
								<FiguresToDate>
									<TaxablePay>800.00</TaxablePay>
									<TotalTax>320.00</TotalTax>
								</FiguresToDate>
								<Payment>
									<BacsHashCode>34567890abcdef1234567890abcdef1234567890abcdef1234567890abcdef12</BacsHashCode>
									<PayFreq>M1</PayFreq>
									<PmtDate>2013-04-30</PmtDate>
									<MonthNo>2</MonthNo>
									<PeriodsCovered>1</PeriodsCovered>
									<HoursWorked>A</HoursWorked>
									<TaxCode BasisNonCumulative="yes">D0</TaxCode>
									<TaxablePay>400.00</TaxablePay>
									<TaxDeductedOrRefunded>160.00</TaxDeductedOrRefunded>
									<SMPYTD>257.46</SMPYTD>
								</Payment>
							</Employment>
						</Employee>
					</FullPaymentSubmission>
				</IRenvelope>';
		}

	}

?>