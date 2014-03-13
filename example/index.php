<?php

//--------------------------------------------------
// Config

		// This is intended to work with PHP Prime,
		// but could be easily re-written to not
		// have any dependencies.

	define('ROOT', dirname(__FILE__));
	define('SERVER', 'stage');
	define('ENCRYPTION_KEY', 'ViJ+P9mW/74C{/BEL');
	define('FRAMEWORK_INIT_ONLY', true);

	require_once(ROOT . '/../../craig.framework/framework/0.1/bootstrap.php');

//--------------------------------------------------
// Required files

	require_once('../hmrc-gateway.php');
	require_once('../hmrc-gateway-message.php');
	require_once('../hmrc-rti.php');
	require_once('../hmrc-rti-fps.php');
	require_once('../hmrc-rti-eas.php');

	require_once('./config.php');

//--------------------------------------------------
// Testing

	header('Content-Type: text/plain; charset=UTF-8');

//--------------------------------------------------
// Gateway setup

	$hmrc_gateway = new hmrc_gateway();
	$hmrc_gateway->live_set(false, true);
	$hmrc_gateway->log_table_set($db, DB_PREFIX . 'table_name');
	$hmrc_gateway->sender_set($config_sender_name, $config_sender_pass, $config_sender_email);

//--------------------------------------------------
// Example employee

	$example_employee = array(

			'national_insurance_number' => 'AB164231A',

			'name' => array(
					'title' => 'Mr',
					'forename' => 'John',
					'surname' => 'Smith',
				),

			'address' => array(
					'lines' => array(
							'1 Street',
							'Gosforth',
							'Town',
						),
					'postcode' => 'AA11 1AA',
				),

			'birth_date' => '2000-01-01', // Date of birth
			'gender'     => 'M',          // Current gender
			'pay_id'     => '123-A02',    // Payroll ID

			'to_date_taxable' => '8000.00', // Taxable pay to date in this employment including taxable benefits undertaken through payroll
			'to_date_tax'     => '756.76',  // Total tax to date in this employment including this submission

			'payment_frequency' => 'M1',         // Pay frequency (e.g. W1 = Weekly, W2 = Fortnightly, W4 = 4 Weekly, M1 = Calendar Monthly, etc)
			'payment_date'      => '2013-05-31', // Payment date
			'payment_month'     => '2',          // Monthly period number
			'payment_periods'   => '1',          // Number of earnings periods covered by payment
			'payment_hours'     => '37.5',       // Number of normal hours worked (approximately)
			'payment_tax_code'  => '810L',       // Tax code and basis
			'payment_taxable'   => '4000.00',    // Taxable pay in this pay period including payrolled benefits in kind
			'payment_tax'       => '756.76',     // Value of tax deducted or refunded from this payment

			'ni_letter' => 'A', // National Insurance Category letter in pay period

			'ni_gross_nics_pd'  => '4000.00', // Gross earnings for NICs in this period.
			'ni_gross_nics_ytd' => '8000.00', // Gross earnings for NICs year to date.

			'ni_total_nic_pd'  => '465.88',  // Total of employer NI Contributions in this period.
			'ni_total_nic_ytd' => '931.76',  // Total of employer NI contributions year to date.

			'ni_total_lel_ytd' => '928.00',  // Value of Earnings at Lower Earnings Limit Year to Date.
			'ni_total_pt_ytd'  => '340.00',  // Value of Earnings above Lower Earnings Limit to Primary Threshold Year to Date.
			'ni_total_uap_ytd' => '5406.00', // Value of Earnings from the Primary Threshold to Upper Accrual Point Year to Date.
			'ni_total_uel_ytd' => '406.00',  // Value of Earnings from Upper Accrual Point up to Upper Earnings Limit Year to Date.

			'ni_total_contribution_pd'  => '0.00', // Employees contributions due on all earnings in this pay period.
			'ni_total_contribution_ytd' => '0.00', // Employees contributions due on all earnings year to date.

				// Calculations: http://www.hmrc.gov.uk/rti/developerfaqs.htm

		);

//--------------------------------------------------
// Final submission information

	$final = false;

	if ($final) {

		$final = array(

				'free_of_tax_payments'         => false, // true if you made any payments to any employees while they were employed by you where you paid their tax on their behalf.
				'expenses_and_benefits'        => false, // true if anyone, other than you, paid expenses or provided benefits to any of your employees during the year as a result of the employee working for you, and while they were employed by you.
				'employees_out_of_uk'          => false, // true if anyone employed by a person or company outside the UK worked for you in the UK for 30 or more days in a row.
				'employees_pay_to_third_party' => false, // true if you have paid any of an employee's pay to someone other than the employee, for example, paying school fees directly to a school - but note that this does not include Attachment of Earnings Orders, payments to the Child Support Agency and Salary Sacrifice arrangements.
				'p11d_forms_due'               => false, // true if any completed forms P11D and P11D(b) are due for the year.
				'service_company'              => true,  // true if you are a service company - 'service company' includes a limited company, a limited liability partnership or a partnership (but not a sole trader) - and have operated the Intermediaries legislation (Chapter 8, Part 2, Income Tax (Earnings and Pensions) Act 2003 (ITEPA), sometimes known as IR35).

					// http://webarchive.nationalarchives.gov.uk/+/http://www.hmrc.gov.uk/employers/2007-08-P35-Quest-6.htm
					// Question 6 should be answered yes if:
					// - an individual personally performs services for a client and the services are provided not
					//   under a contract directly between the client and the worker but under arrangements involving
					//   the limited company, limited liability partnership or general partnership (the service company).
					// - the limited company, limited liability partnership or general partnership's (the service company)
					//   business consists wholly or mainly of providing the services of individuals to clients.

			);

	}

//--------------------------------------------------
// Delete requests

	// $requests = $hmrc_gateway->request_list('HMRC-PAYE-RTI-EAS');
	// print_r($requests);
	// foreach ($requests as $request) {
	// 	$hmrc_gateway->request_delete($request);
	// }
	// exit('Deleted');

	// $hmrc_gateway->request_delete(array(
	// 		'class' => 'HMRC-PAYE-RTI-EAS',
	// 		'correlation' => 'DF64ED198BEB43178A0C6A3CCE7D389C',
	// 	));

//--------------------------------------------------
// Pending requests

	// $requests = $hmrc_gateway->request_list('HMRC-PAYE-RTI-EAS');
	// print_r($requests);
	// exit();

	// $requests = $hmrc_gateway->request_list('HMRC-PAYE-RTI-FPS');
	// foreach ($requests as $request) {
	//
	// 	print_r($request);
	// 	$request = $hmrc_gateway->request_poll($request);
	// 	print_r($request);
	// 	exit();
	//
	// }

//--------------------------------------------------
// Create request

	$hmrc_rti = new hmrc_rti_eas();
	// $hmrc_rti = new hmrc_rti_fps();

	$hmrc_rti->details_set(array(
			'year' => 2013,
			'final' => $final,
			'tax_office_number' => $config_tax_office_number,
			'tax_office_reference' => $config_tax_office_reference,
			'accounts_office_reference' => $config_accounts_office_reference,
			'corporation_tax_reference' => $config_corporation_tax_reference,
		));

	$hmrc_rti->employee_add($example_employee);

//--------------------------------------------------
// Send and poll for response

	$request = $hmrc_gateway->request_submit($hmrc_rti);

	print_r($request);

	$k = 0;

	while ($request['status'] === NULL && $k++ < 5) {

		$request = $hmrc_gateway->request_poll($request);

		print_r($request);

	}

	if ($request['status'] === NULL) {
		exit('Stopped waiting for a response after several attempts.');
	}

?>