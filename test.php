<?php

//--------------------------------------------------
// Config

		// This is intended to work with PHP Prime,
		// but could be re-written to not have any
		// dependencies.

	define('ROOT', dirname(__FILE__));
	define('SERVER', 'stage');
	define('ENCRYPTION_KEY', 'ViJ+P9mW/74C{/BEL');
	define('FRAMEWORK_INIT_ONLY', true);

	require_once(ROOT . '/../craig.framework/framework/0.1/bootstrap.php');

//--------------------------------------------------
// Required files

	require_once('./lib/hmrc-gateway.php');
	require_once('./lib/hmrc-gateway-message.php');
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
	$hmrc_gateway->message_key_add('TaxOfficeNumber', $config_office_number);
	$hmrc_gateway->message_key_add('TaxOfficeReference', $config_office_reference);

//--------------------------------------------------
// Delete requests

	if (true) {

		$requests = $hmrc_gateway->request_list('HMRC-PAYE-RTI-FPS');
		foreach ($requests as $request) {
			$hmrc_gateway->request_delete($request);
		}

		// exit('Deleted');

	}

	// $hmrc_gateway->request_delete(array(
	// 		'class' => 'HMRC-PAYE-RTI-FPS',
	// 		'correlation' => 'DF64ED198BEB43178A0C6A3CCE7D389C',
	// 	));

//--------------------------------------------------
// Pending requests

	if (false) {

		$requests = $hmrc_gateway->request_list('HMRC-PAYE-RTI-FPS');
		foreach ($requests as $request) {

			print_r($request);
			$request = $hmrc_gateway->request_poll($request);
			print_r($request);

		}

		exit();

	}

//--------------------------------------------------
// Create request

	$hmrc_rti = new hmrc_rti_fps();
	$hmrc_rti->employee_add('XXX');

	$request = $hmrc_gateway->request_submit($hmrc_rti);

	// print_r($request);

//--------------------------------------------------
// Poll for response

	$k = 0;

	while ($request['status'] === NULL && $k++ < 5) {

		$request = $hmrc_gateway->request_poll($request);

		// print_r($request);

	}

	if ($request['status'] === NULL) {
		exit('Stopped waiting for a response after several attempts.');
	}

?>