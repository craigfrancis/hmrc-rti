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

	require_once(ROOT . '/../craig.framework/framework/0.1/bootstrap.php');

//--------------------------------------------------
// Required files

	require_once('./lib/hmrc-gateway.php');
	require_once('./lib/hmrc-gateway-message.php');
	require_once('./lib/hmrc-rti.php');
	require_once('./lib/hmrc-rti-fps.php');

	require_once('./test-config.php');

//--------------------------------------------------
// Testing

	header('Content-Type: text/plain; charset=UTF-8');

//--------------------------------------------------
// Gateway setup

	$hmrc_gateway = new hmrc_gateway();
	$hmrc_gateway->live_set(false, true);
	$hmrc_gateway->sender_set($config_sender_name, $config_sender_pass, $config_sender_email);
	$hmrc_gateway->message_key_add('TaxOfficeNumber', $config_tax_office_number);
	$hmrc_gateway->message_key_add('TaxOfficeReference', $config_tax_office_reference);

//--------------------------------------------------
// Delete requests

	// $requests = $hmrc_gateway->request_list('HMRC-PAYE-RTI-FPS');
	// foreach ($requests as $request) {
	// 	$hmrc_gateway->request_delete($request);
	// }
	// exit('Deleted');

	// $hmrc_gateway->request_delete(array(
	// 		'class' => 'HMRC-PAYE-RTI-FPS',
	// 		'correlation' => 'DF64ED198BEB43178A0C6A3CCE7D389C',
	// 	));

//--------------------------------------------------
// Pending requests

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

	$hmrc_rti = new hmrc_rti_fps();

	$hmrc_rti->details_set(array(
			'year' => 2013,
			'accounts_office_reference' => $config_accounts_office_reference,
			'corporation_tax_reference' => $config_corporation_tax_reference,
		));

	$hmrc_rti->employee_add(array(

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

			'payment_freqency' => 'M1',         // Pay frequency (e.g. W1 = Weekly, W2 = Fortnightly, W4 = 4 Weekly, M1 = Calendar Monthly, etc)
			'payment_date'     => '2013-05-31', // Payment date
			'payment_month'    => '2',          // Monthly period number
			'payment_periods'  => '1',          // Number of earnings periods covered by payment
			'payment_hours'    => 'B',          // Number of normal hours worked (A, B = 16+ 23.99 hours, C = 24+ hours, D = 30+ hours, E = Other)
			'payment_tax_code' => '810L',       // Tax code and basis
			'payment_taxable'  => '4000.00',    // Taxable pay in this pay period including payrolled benefits in kind
			'payment_tax'      => '756.76',     // Value of tax deducted or refunded from this payment

			'ni_letter' => 'A', // National Insurance Category letter in pay period

			'ni_gross_nics_pd'  => '4000.00', // Gross earnings for NICs in this period.
			'ni_gross_nics_ytd' => '8000.00', // Gross earnings for NICs year to date.

			'ni_total_lel_ytd' => '928.00',  // Value of Earnings at Lower Earnings Limit Year to Date.
			'ni_total_pt_ytd'  => '340.00',  // Value of Earnings above Lower Earnings Limit to Primary Threshold Year to Date.
			'ni_total_uap_ytd' => '5406.00', // Value of Earnings from the Primary Threshold to Upper Accrual Point Year to Date.
			'ni_total_uel_ytd' => '406.00',  // Value of Earnings from Upper Accrual Point up to Upper Earnings Limit Year to Date.

			'ni_total_nic_pd'  => '465.88',  // Total of employer NI Contributions in this period.
			'ni_total_nic_ytd' => '931.76',  // Total of employer NI contributions year to date.

			'ni_total_contribution_pd'  => '0.00', // Employees contributions due on all earnings in this pay period.
			'ni_total_contribution_ytd' => '0.00', // Employees contributions due on all earnings year to date.

				// TODO: Calculations: http://www.hmrc.gov.uk/rti/developerfaqs.htm

		));

	$request = $hmrc_gateway->request_submit($hmrc_rti);

	print_r($request);

//--------------------------------------------------
// Poll for response

	$k = 0;

	while ($request['status'] === NULL && $k++ < 5) {

		$request = $hmrc_gateway->request_poll($request);

		print_r($request);

	}

	if ($request['status'] === NULL) {
		exit('Stopped waiting for a response after several attempts.');
	}

?>